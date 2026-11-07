import { envBoolean, envList, envNumber, securityProfile, solutionProfile } from "noderyx-framework";

const type = (process.env.DB_TYPE ?? "mysql").toLowerCase();
const profile = solutionProfile(process.env.APP_PROFILE ?? "saas");
const selectedSecurity = securityProfile(process.env.SECURITY_PROFILE ?? profile.security.profile ?? "standard");
const aiProviderName = (process.env.AI_PROVIDER ?? "openai").toLowerCase();
const aiProvider = aiProviderName === "claude" ? "anthropic" : aiProviderName;
const usingClaude = aiProvider === "anthropic";

export default {
  app: {
    name: process.env.APP_NAME ?? "sorrydotcomeditor",
    environment: process.env.NODE_ENV ?? "development",
    debug: envBoolean("APP_DEBUG", process.env.NODE_ENV !== "production"),
    url: process.env.APP_URL ?? "http://localhost:3000",
    timezone: process.env.APP_TIMEZONE ?? "UTC",
    locale: process.env.APP_LOCALE ?? "en",
    logLevel: process.env.LOG_LEVEL ?? "info",
    profile: profile.name
  },
  // Local packages under packages/ load automatically. Add published packages here.
  packages: [],
  ai: {
    provider: aiProvider,
    enabled: envBoolean("AI_ENABLED", false),
    apiKey: usingClaude ? process.env.ANTHROPIC_API_KEY : process.env.OPENAI_API_KEY,
    baseURL: usingClaude
      ? process.env.ANTHROPIC_BASE_URL ?? "https://api.anthropic.com/v1"
      : process.env.OPENAI_BASE_URL ?? "https://api.openai.com/v1",
    model: usingClaude
      ? process.env.ANTHROPIC_MODEL ?? "claude-opus-5"
      : process.env.OPENAI_MODEL ?? "gpt-5.6-sol",
    reasoningEffort: process.env.AI_REASONING_EFFORT ?? "medium",
    verbosity: process.env.AI_VERBOSITY ?? "medium",
    maxOutputTokens: envNumber("AI_MAX_OUTPUT_TOKENS", usingClaude ? 4000 : 1200),
    inputLimit: envNumber("AI_INPUT_LIMIT", 8000),
    timeoutMs: envNumber("AI_TIMEOUT_MS", usingClaude ? 60000 : 30000),
    store: envBoolean("AI_STORE", false),
    fallbacks: envBoolean("ANTHROPIC_FALLBACKS", true),
    instructions: process.env.AI_INSTRUCTIONS ?? "Be practical, concise, and honest."
  },
  database: type.startsWith("post")
    ? { type: "postgres", connectionString: process.env.DATABASE_URL }
    : type.startsWith("mongo")
      ? { type: "mongo", url: process.env.MONGODB_URL, database: process.env.DB_NAME }
      : {
          type: "mysql",
          host: process.env.DB_HOST,
          port: envNumber("DB_PORT", 3306),
          user: process.env.DB_USER ?? "root",
          password: process.env.DB_PASSWORD ?? "",
          database: process.env.DB_NAME,
          connectionLimit: envNumber("DB_POOL_MAX", 10)
        },
  migrations: "database/migrations",
  seeders: "database/seeders",

  cache: {
    driver: process.env.CACHE_DRIVER ?? "memory",
    prefix: process.env.CACHE_PREFIX ?? "sorrydotcomeditor",
    ttl: envNumber("CACHE_TTL", profile.cache.ttl),
    maxItems: envNumber("CACHE_MAX_ITEMS", profile.cache.maxItems),
    staticMaxAge: envNumber("CACHE_STATIC_MAX_AGE", profile.cache.staticMaxAge),
    staleWhileRevalidate: envNumber("CACHE_STALE_WHILE_REVALIDATE", profile.cache.staleWhileRevalidate)
  },

  mail: {
    driver: process.env.MAIL_DRIVER ?? "log",
    host: process.env.MAIL_HOST ?? "127.0.0.1",
    port: envNumber("MAIL_PORT", 1025),
    secure: envBoolean("MAIL_SECURE", false),
    username: process.env.MAIL_USERNAME ?? null,
    password: process.env.MAIL_PASSWORD ?? null,
    from: {
      address: process.env.MAIL_FROM_ADDRESS ?? "hello@example.com",
      name: process.env.MAIL_FROM_NAME ?? process.env.APP_NAME ?? "sorrydotcomeditor"
    }
  },

  security: {
    ...selectedSecurity,
    appKey: process.env.APP_KEY,
    trustProxy: envBoolean("TRUST_PROXY", false),
    bodyLimit: envNumber("REQUEST_BODY_LIMIT", selectedSecurity.bodyLimit ?? profile.security.bodyLimit),
    cors: envList("CORS_ORIGINS"),
    rateLimit: {
      ...selectedSecurity.rateLimit,
      windowMs: envNumber("RATE_LIMIT_WINDOW_MS", selectedSecurity.rateLimit.windowMs ?? 60000),
      max: envNumber("RATE_LIMIT_MAX", selectedSecurity.rateLimit.max ?? profile.security.rateLimitMax)
    },
    session: {
      ...selectedSecurity.session,
      name: process.env.SESSION_COOKIE ?? "noderyx_session",
      maxAge: envNumber("SESSION_MAX_AGE", selectedSecurity.session.maxAge ?? 604800),
      sameSite: process.env.SESSION_SAME_SITE ?? selectedSecurity.session.sameSite ?? "Lax",
      secure: envBoolean("SESSION_SECURE", process.env.NODE_ENV === "production")
    }
  },

  // Android and iOS builds: noderyx mobile:init
  mobile: {
    appId: process.env.MOBILE_APP_ID ?? "com.sorrydotcom.editor",
    appName: process.env.MOBILE_APP_NAME ?? "sorrydotcomeditor",
    views: "resources/views",
    public: "public",
    entry: "home",
    out: "platforms/mobile",
    // The packaged app has no server of its own, so point it at your API.
    apiUrl: process.env.MOBILE_API_URL ?? null,
    // Installed apps fetch theme, text, flags, and screens published from the
    // web app (Mobile Studio at /mobile-studio), so no store release is needed.
    remoteUi: true,
    // Several apps from one project. Each gets its own screens
    // (resources/mobile/<name>), app ID, and Android/iOS projects under
    // platforms/mobile/<name>. Add one with: noderyx mobile:app <name>
    // apps: {
    //   customer: { appId: "com.example.customer", appName: "Example" },
    //   admin: { appId: "com.example.admin", appName: "Example Admin" },
    //   partner: { appId: "com.example.partner", appName: "Example Partner" }
    // },
    data: {
      siteName: process.env.SITE_NAME ?? "sorrydotcomeditor",
      siteUrl: process.env.SITE_URL ?? "http://localhost:3000",
      description: process.env.SITE_DESCRIPTION ?? "A Noderyx Framework starter application."
    }
  },

  // Native Android and iOS: noderyx native:init
  // Screens draw real platform widgets. There is no WebView.
  native: {
    appId: process.env.MOBILE_APP_ID ?? "com.sorrydotcom.editor",
    appName: process.env.MOBILE_APP_NAME ?? "sorrydotcomeditor",
    views: "resources/mobile",
    out: "platforms/native",
    entry: "home",
    // The app has no server of its own, so point it at yours.
    apiUrl: process.env.MOBILE_API_URL ?? null
  },

  // Windows and macOS apps: noderyx desktop:init
  // App ID, name, views, and API URL fall back to the mobile block.
  desktop: {
    out: "platforms/desktop",
    window: { width: 1200, height: 800, minWidth: 720, minHeight: 480 }
  }
};
