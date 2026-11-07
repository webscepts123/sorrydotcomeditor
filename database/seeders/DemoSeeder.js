// Repeatable sample data: existing sample projects and user records are preserved.
export async function run(db) {
  if (db.kind !== "mysql") throw new Error("Demo data requires MySQL or MariaDB.");
  await db.transaction(async (connection) => {
    const query = async (sql, values = []) => (await connection.execute(sql, values))[0];
    const insert = async (table, values) => {
      const fields = Object.keys(values);
      const result = await query(
        `INSERT INTO ${table} (${fields.join(", ")}) VALUES (${fields.map(() => "?").join(", ")})`,
        Object.values(values)
      );
      return result.insertId;
    };
    const users = await query("SELECT id FROM users ORDER BY id LIMIT 1");
    const owner = users[0]?.id ?? null;
    const samples = [
      { slug: "demo-last-train", title: "[Demo] The Last Train", style: "Cinematic noir", ratio: "16:9", status: "draft", hero: "Maya", role: "Station engineer", story: "An engineer discovers a final train carrying a message from her future self.", scenes: ["Maya unlocks the deserted station as rain rolls across the platform.", "An empty train arrives. A sealed letter rests on the driver's seat.", "Maya reads the warning and races toward the signal tower.", "At sunrise, she changes the signal and watches a new future unfold."] },
      { slug: "demo-ocean-light", title: "[Demo] Ocean of Light", style: "Warm documentary", ratio: "16:9", status: "draft", hero: "Arun", role: "Marine researcher", story: "A researcher and a local guide search for a rare glowing reef.", scenes: ["Arun prepares his research equipment beside a quiet fishing harbour.", "The crew follows a trail of glowing plankton under a moonlit sky.", "A hidden reef lights up as the divers approach in silence."] },
      { slug: "demo-city-coffee", title: "[Demo] City Coffee", style: "Bright commercial", ratio: "9:16", status: "draft", hero: "Nila", role: "Cafe owner", story: "A small cafe brings a busy neighbourhood together over one perfect cup.", scenes: ["Nila opens the cafe and grinds fresh beans in the morning light.", "Neighbours pause their busy mornings to share coffee and conversation.", "A close-up of a steaming cup reveals the campaign message: Make time to connect."] }
    ];
    let added = 0;
    for (const sample of samples) {
      const existing = await query("SELECT id FROM projects WHERE slug = ? LIMIT 1", [sample.slug]);
      if (existing.length) continue;
      const project = await insert("projects", { user_id: owner, title: sample.title, slug: sample.slug, description: sample.story, aspect_ratio: sample.ratio, resolution: "1080p", style_preset: sample.style, status: sample.status, total_scenes: sample.scenes.length, completed_clips: 0 });
      const character = await insert("characters", { project_id: project, name: sample.hero, ai_tag: `@${sample.hero.toLowerCase()}`, role: sample.role, description: `${sample.hero}, a thoughtful and resourceful ${sample.role.toLowerCase()}.`, personality: "Curious, determined, empathetic", dialogue_style: "Natural and concise", prompt: `Consistent portrait of ${sample.hero}, ${sample.style.toLowerCase()}`, face_sync_status: "pending" });
      for (const [index, segment] of sample.scenes.entries()) {
        const scene = await insert("scenes", { project_id: project, character_id: character, order_index: index + 1, script_segment: segment, prompt: `${sample.style}. ${segment}`, duration: 8, status: index === 0 ? "Ready" : "Draft" });
        await insert("character_scene", { character_id: character, scene_id: scene });
        await insert("video_clips", { scene_id: scene, prompt: segment, duration: 8, status: "pending" });
      }
      await insert("production_plans", { project_id: project, logline: sample.story, full_story: sample.scenes.join("\n\n"), script_outline: sample.scenes.map((text, i) => `${i + 1}. ${text}`).join("\n"), scene_breakdown: sample.scenes.map((text, i) => `Scene ${i + 1}: 8 seconds. ${text}`).join("\n"), schedule_start_date: "2026-11-09", schedule_end_date: "2026-11-11", shooting_days: 3, daily_call_time: "08:00:00", render_deadline: "2026-11-13", release_target: "2026-11-16", production_notes: "Dummy data for checking the editor. Media generation has not been started." });
      await insert("soundtracks", { project_id: project, title: `${sample.title} - Main Theme`, composer: "Demo Studio", type: "background", notes: "Placeholder music brief; no audio file attached.", status: "draft" });
      await insert("trailers", { project_id: project, title: `${sample.title} - Teaser`, description: "A short teaser assembled from the opening scenes. Placeholder brief only.", duration: "15", status: "draft" });
      const email = `demo-${sample.slug}@example.test`;
      let editors = await query("SELECT id FROM editors WHERE email = ? LIMIT 1", [email]);
      const editor = editors[0]?.id ?? await insert("editors", { name: `Demo Editor ${added + 1}`, email, role: "Video editor", is_active: 1 });
      await insert("editor_project", { editor_id: editor, project_id: project, assigned_task: "Review storyboard and prepare the first cut" });
      added++;
    }
    console.log(`Added ${added} demo projects with characters, scenes, clip briefs, plans, soundtracks, trailers, and editors.`);
    console.log(owner ? "Demo projects belong to the first existing user." : "Complete /setup to create your account and claim the demo projects.");
  });
}
