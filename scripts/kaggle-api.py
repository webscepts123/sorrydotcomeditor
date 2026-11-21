"""Private JSON bridge to the official Kaggle CLI library. Credentials use stdin."""
import contextlib
import io
import json
import os
from pathlib import Path
import sys
import tempfile


class BridgeError(RuntimeError):
    """An error safe to show without revealing the provider response."""


def run(data):
    action = data["action"]
    for key in ("KAGGLE_API_TOKEN", "KAGGLE_USERNAME", "KAGGLE_KEY"):
        os.environ.pop(key, None)
    if action != "probe":
        credentials = data["credentials"]
        os.environ["KAGGLE_USERNAME"] = credentials["username"]
        os.environ["KAGGLE_API_TOKEN" if credentials["kind"] == "token" else "KAGGLE_KEY"] = credentials["secret"]
    with tempfile.TemporaryDirectory(prefix="studio-kaggle-auth-") as config:
        os.environ["KAGGLE_CONFIG_DIR"] = config
        # The SDK also reads OAuth credentials and token files from the user's home.
        os.environ["HOME"] = config
        os.environ["USERPROFILE"] = config
        from kaggle.api.kaggle_api_extended import KaggleApi
        if action == "probe":
            from importlib.metadata import version
            return {"version": version("kaggle")}
        api = KaggleApi()
        try:
            api.authenticate()
        except SystemExit:
            raise BridgeError("Kaggle did not accept this credential. Generate a fresh API token or paste the legacy kaggle.json.") from None
        authenticated = api.config_values.get(api.CONFIG_NAME_USER, "")
        if authenticated.lower() != credentials["username"].lower():
            raise BridgeError("This API token belongs to a different Kaggle username.")
        if action == "verify":
            api.kernels_list(mine=True, page_size=1)
            return {"connected": True}
        if action == "submit":
            result = api.kernels_push(data["directory"], acc="NvidiaTeslaT4")
            if result is None or getattr(result, "error", None):
                raise BridgeError("Kaggle rejected the notebook. Check GPU access and account verification.")
            return {"submitted": True}
        if action == "status":
            result = api.kernels_status(data["kernel"])
            state = getattr(result.status, "name", str(result.status)).lower()
            return {"status": state}
        if action == "output":
            token = None
            files = []
            for _ in range(20):
                batch, token = api.kernels_output(data["kernel"], data["directory"], file_pattern=r"-1080p\.mp4$", page_token=token, quiet=True)
                files.extend(batch)
                if not token:
                    break
            videos = [str(Path(file).resolve()) for file in files if Path(file).is_file()]
            if len(videos) != 1:
                raise BridgeError("The run did not produce one 1080p MP4. Open the Kaggle notebook to inspect its output.")
            return {"video": videos[0]}
        raise ValueError("Unknown action")


try:
    payload = json.load(sys.stdin)
    with contextlib.redirect_stdout(io.StringIO()), contextlib.redirect_stderr(io.StringIO()):
        result = run(payload)
    print(json.dumps(result))
except BaseException as error:
    # Do not return SDK response bodies: they may contain credentials or signed URLs.
    status = getattr(getattr(error, "response", None), "status_code", None)
    if isinstance(error, (ImportError, ModuleNotFoundError)) or type(error).__name__ == "PackageNotFoundError":
        message = "The server Kaggle runtime is not installed. Ask the administrator to configure Kaggle."
    elif isinstance(error, BridgeError):
        message = str(error)
    elif status == 401:
        message = "Kaggle rejected the API credential. Generate a fresh token and confirm the Kaggle username."
    elif status == 403:
        message = "Kaggle denied API access. Check that this credential can access Kaggle Notebooks."
    elif status == 429:
        message = "Kaggle is limiting API requests. Wait a few minutes and try again."
    elif isinstance(status, int) and status >= 500:
        message = "Kaggle is temporarily unavailable. Try again shortly."
    else:
        message = "The studio could not complete the Kaggle API request. Try again shortly."
    print(json.dumps({"error": message}))
    sys.exit(1)
