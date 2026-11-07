const tables = [
  `CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )`,
  `CREATE TABLE IF NOT EXISTS projects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NULL,
    description TEXT NULL,
    aspect_ratio VARCHAR(32) NULL,
    resolution VARCHAR(32) NULL,
    style_preset VARCHAR(255) NULL,
    poster_image VARCHAR(255) NULL,
    teaser_video VARCHAR(255) NULL,
    final_movie_path VARCHAR(255) NULL,
    total_scenes INT NOT NULL DEFAULT 0,
    completed_clips INT NOT NULL DEFAULT 0,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX projects_user_id_index (user_id)
  )`,
  `CREATE TABLE IF NOT EXISTS editors (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    role VARCHAR(255) NULL,
    avatar_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )`,
  `CREATE TABLE IF NOT EXISTS characters (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    ai_tag VARCHAR(255) NULL,
    role VARCHAR(100) NULL,
    reference_image VARCHAR(255) NULL,
    image_path VARCHAR(255) NULL,
    description TEXT NULL,
    personality TEXT NULL,
    dialogue_style TEXT NULL,
    prompt TEXT NULL,
    video_prompt TEXT NULL,
    face_sync_status VARCHAR(32) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX characters_project_id_index (project_id)
  )`,
  `CREATE TABLE IF NOT EXISTS scenes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NULL,
    order_index INT NOT NULL DEFAULT 0,
    script_segment TEXT NULL,
    video_path VARCHAR(255) NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'pending',
    generation_job_id VARCHAR(255) NULL,
    generation_error TEXT NULL,
    duration INT NULL,
    prompt LONGTEXT NULL,
    video_url VARCHAR(2048) NULL,
    seedance_job_id VARCHAR(255) NULL,
    character_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX scenes_project_id_order_index (project_id, order_index)
  )`,
  `CREATE TABLE IF NOT EXISTS movies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    synopsis TEXT NULL,
    global_style VARCHAR(255) NULL,
    poster_path VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )`,
  `CREATE TABLE IF NOT EXISTS video_clips (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    scene_id BIGINT UNSIGNED NULL,
    file_path VARCHAR(255) NULL,
    video_path VARCHAR(255) NULL,
    last_frame_path VARCHAR(255) NULL,
    prompt TEXT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'pending',
    duration INT NULL,
    seedance_tag VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX video_clips_scene_id_index (scene_id)
  )`,
  `CREATE TABLE IF NOT EXISTS soundtracks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    movie_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    composer VARCHAR(255) NULL,
    type VARCHAR(64) NULL,
    notes TEXT NULL,
    file_path VARCHAR(255) NULL,
    status VARCHAR(32) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )`,
  `CREATE TABLE IF NOT EXISTS trailers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    video_path VARCHAR(255) NULL,
    duration VARCHAR(32) NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )`,
  `CREATE TABLE IF NOT EXISTS production_plans (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL UNIQUE,
    logline VARCHAR(255) NULL,
    full_story LONGTEXT NULL,
    script_outline LONGTEXT NULL,
    scene_breakdown LONGTEXT NULL,
    schedule_start_date DATE NULL,
    schedule_end_date DATE NULL,
    shooting_days INT UNSIGNED NULL,
    daily_call_time TIME NULL,
    render_deadline DATE NULL,
    release_target DATE NULL,
    production_notes LONGTEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )`,
  `CREATE TABLE IF NOT EXISTS editor_project (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    editor_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    assigned_task VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY editor_project_pair_unique (editor_id, project_id)
  )`,
  `CREATE TABLE IF NOT EXISTS character_scene (
    character_id BIGINT UNSIGNED NOT NULL,
    scene_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (character_id, scene_id)
  )`,
  `CREATE TABLE IF NOT EXISTS auths (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )`
];

const columns = {
  projects: {
    user_id: "BIGINT UNSIGNED NULL",
    slug: "VARCHAR(255) NULL",
    resolution: "VARCHAR(32) NULL",
    poster_image: "VARCHAR(255) NULL",
    teaser_video: "VARCHAR(255) NULL",
    final_movie_path: "VARCHAR(255) NULL",
    total_scenes: "INT NOT NULL DEFAULT 0",
    completed_clips: "INT NOT NULL DEFAULT 0",
    status: "VARCHAR(32) NOT NULL DEFAULT 'draft'"
  },
  editors: { avatar_path: "VARCHAR(255) NULL", is_active: "TINYINT(1) NOT NULL DEFAULT 1" },
  characters: { face_sync_status: "VARCHAR(32) NULL" },
  scenes: { generation_job_id: "VARCHAR(255) NULL", generation_error: "TEXT NULL", duration: "INT NULL", prompt: "LONGTEXT NULL", video_url: "VARCHAR(2048) NULL", seedance_job_id: "VARCHAR(255) NULL" },
  video_clips: { file_path: "VARCHAR(255) NULL", video_path: "VARCHAR(255) NULL", last_frame_path: "VARCHAR(255) NULL", prompt: "TEXT NULL", seedance_tag: "VARCHAR(255) NULL" },
  soundtracks: { project_id: "BIGINT UNSIGNED NULL", composer: "VARCHAR(255) NULL", notes: "TEXT NULL", status: "VARCHAR(32) NULL" }
};

export async function up(db) {
  if (db.kind !== "mysql") throw new Error("The existing sorrydotcomeditor schema migration currently requires MySQL.");
  for (const statement of tables) await db.query(statement);
  for (const [table, fields] of Object.entries(columns)) {
    for (const [column, type] of Object.entries(fields)) {
      const existing = await db.query(
        "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
        [table, column]
      );
      if (!existing.length) await db.query(`ALTER TABLE ${table} ADD COLUMN ${column} ${type}`);
    }
  }
  await db.query("UPDATE projects SET total_scenes = (SELECT COUNT(*) FROM scenes WHERE scenes.project_id = projects.id)");
}

export async function down(db) {
  if (db.kind !== "mysql") throw new Error("The existing sorrydotcomeditor schema migration currently requires MySQL.");
  throw new Error("This migration preserves the existing production data and cannot be rolled back by dropping tables.");
}
