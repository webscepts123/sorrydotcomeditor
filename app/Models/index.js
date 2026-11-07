const definitions = {
  users: ["name", "email", "password"],
  projects: ["user_id", "title", "slug", "description", "aspect_ratio", "resolution", "style_preset", "poster_image", "teaser_video", "final_movie_path", "total_scenes", "completed_clips", "status"],
  scenes: ["project_id", "order_index", "script_segment", "video_path", "video_url", "status", "generation_job_id", "generation_error", "duration", "prompt", "seedance_job_id"],
  characters: ["project_id", "name", "ai_tag", "role", "reference_image", "image_path", "description", "personality", "dialogue_style", "prompt", "video_prompt", "face_sync_status"],
  editors: ["name", "email", "role", "avatar_path", "is_active"],
  movies: ["title", "synopsis", "global_style", "poster_path"],
  video_clips: ["scene_id", "file_path", "video_path", "last_frame_path", "prompt", "status", "duration", "seedance_tag"],
  soundtracks: ["movie_id", "project_id", "title", "composer", "type", "notes", "file_path", "status"],
  trailers: ["project_id", "title", "description", "video_path", "duration", "status"],
  production_plans: ["project_id", "logline", "full_story", "script_outline", "scene_breakdown", "schedule_start_date", "schedule_end_date", "shooting_days", "daily_call_time", "render_deadline", "release_target", "production_notes"]
};

const identifier = /^[a-z][a-z0-9_]*$/;

export class Repository {
  constructor(database, table) {
    if (!Object.hasOwn(definitions, table)) throw new TypeError(`Unknown model table: ${table}`);
    this.database = database;
    this.table = table;
  }

  clean(values) {
    const allowed = new Set(definitions[this.table]);
    return Object.fromEntries(Object.entries(values).filter(([key, value]) =>
      allowed.has(key) && value !== undefined
    ));
  }

  async all({ where = "", values = [], orderBy = "id DESC", limit = 200 } = {}) {
    if (!/^[a-z0-9_., ]+(?:ASC|DESC)?$/i.test(orderBy)) throw new TypeError("Invalid sort order");
    const suffix = where ? ` WHERE ${where}` : "";
    return this.database.query(
      `SELECT * FROM ${this.table}${suffix} ORDER BY ${orderBy} LIMIT ${Number(limit)}`,
      values
    );
  }

  async find(id) {
    const rows = await this.database.query(`SELECT * FROM ${this.table} WHERE id = ? LIMIT 1`, [id]);
    return rows[0] ?? null;
  }

  async create(values) {
    const data = this.clean(values);
    const entries = Object.entries(data);
    if (!entries.length) throw new TypeError(`No writable fields were supplied for ${this.table}`);
    const columns = entries.map(([column]) => {
      if (!identifier.test(column)) throw new TypeError("Invalid field name");
      return column;
    });
    const result = await this.database.query(
      `INSERT INTO ${this.table} (${columns.join(", ")}) VALUES (${columns.map(() => "?").join(", ")})`,
      entries.map(([, value]) => value)
    );
    return this.find(result.insertId);
  }

  async update(id, values) {
    const entries = Object.entries(this.clean(values));
    if (!entries.length) return this.find(id);
    const assignments = entries.map(([column]) => {
      if (!identifier.test(column)) throw new TypeError("Invalid field name");
      return `${column} = ?`;
    });
    await this.database.query(
      `UPDATE ${this.table} SET ${assignments.join(", ")}, updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
      [...entries.map(([, value]) => value), id]
    );
    return this.find(id);
  }

  async delete(id) {
    const result = await this.database.query(`DELETE FROM ${this.table} WHERE id = ?`, [id]);
    return result.affectedRows > 0;
  }
}

export function repositories(database) {
  return Object.fromEntries(Object.keys(definitions).map((table) => [table, new Repository(database, table)]));
}
