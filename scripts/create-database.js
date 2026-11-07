import { loadEnvironment } from "noderyx-framework";
import mysql from "mysql2/promise";

loadEnvironment();
const { default: config } = await import("../noderyx.config.js");

if (config.database.type === "mysql") {
  const { type, database, ...options } = config.database;
  if (!database?.trim()) throw new Error("Set DB_NAME in .env before creating the database.");
  const connection = await mysql.createConnection(options);
  try {
    await connection.query(
      `CREATE DATABASE IF NOT EXISTS ${mysql.escapeId(database)} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`
    );
    console.log(`Database ready: ${database}`);
  } finally {
    await connection.end();
  }
}
