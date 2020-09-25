CREATE TABLE IF NOT EXISTS "migrations" ("id" integer not null primary key autoincrement, "migration" varchar not null, "batch" integer not null);
CREATE TABLE IF NOT EXISTS "users" ("id" integer not null primary key autoincrement, "username" varchar not null, "phone" varchar not null, "email" varchar not null, "email_verified_at" datetime null, "phone_verified_at" datetime null, "password" varchar not null, "remember_token" varchar null, "created_at" datetime null, "updated_at" datetime null, "password_token" varchar null, "token_expiry" datetime null, "referrer" varchar null);
CREATE UNIQUE INDEX "users_username_unique" on "users" ("username");
CREATE UNIQUE INDEX "users_phone_unique" on "users" ("phone");
CREATE UNIQUE INDEX "users_email_unique" on "users" ("email");
CREATE TABLE IF NOT EXISTS "password_resets" ("email" varchar not null, "token" varchar not null, "created_at" datetime null, "token_expiry" datetime null);
CREATE INDEX "password_resets_email_index" on "password_resets" ("email");
CREATE TABLE IF NOT EXISTS "failed_jobs" ("id" integer not null primary key autoincrement, "connection" text not null, "queue" text not null, "payload" text not null, "exception" text not null, "failed_at" datetime default CURRENT_TIMESTAMP not null);
CREATE TABLE IF NOT EXISTS "profiles" ("id" integer not null primary key autoincrement, "user_id" integer not null, "first_name" varchar not null, "last_name" varchar not null, "gender" varchar check ("gender" in ('male', 'female')) null, "date_of_birth" date null, "address" varchar null, "state" varchar null, "avatar" varchar null, "account_name" varchar null, "bank_name" varchar null, "account_number" varchar null, "currency" varchar null, "created_at" datetime null, "updated_at" datetime null, "referral_code" varchar null, foreign key("user_id") references "users"("id"));
CREATE TABLE IF NOT EXISTS "categories" ("id" integer not null primary key autoincrement, "name" varchar not null, "description" varchar not null, "instruction" varchar not null, "primary_color" varchar null, "icon_name" varchar null, "game_background_url" varchar null, "category_id" integer null, "created_at" datetime null, "updated_at" datetime null);
CREATE TABLE IF NOT EXISTS "questions" ("id" integer not null primary key autoincrement, "label" varchar not null, "level" varchar check ("level" in ('easy', 'medium', 'hard')) not null, "category_id" integer not null, "created_at" datetime null, "updated_at" datetime null, foreign key("category_id") references "categories"("id"));
CREATE INDEX "questions_level_index" on "questions" ("level");
CREATE TABLE IF NOT EXISTS "options" ("id" integer not null primary key autoincrement, "question_id" integer not null, "title" varchar not null, "is_correct" tinyint(1) not null, "created_at" datetime null, "updated_at" datetime null, foreign key("question_id") references "questions"("id"));
CREATE INDEX "options_is_correct_index" on "options" ("is_correct");
CREATE TABLE IF NOT EXISTS "plans" ("id" integer not null primary key autoincrement, "name" varchar not null, "description" varchar not null, "price" numeric not null, "games_count" integer not null, "point_per_question" integer not null, "minimum_win_points" integer not null, "price_per_point" numeric not null, "created_at" datetime null, "updated_at" datetime null);
CREATE TABLE IF NOT EXISTS "user_plan" ("id" integer not null primary key autoincrement, "user_id" integer not null, "plan_id" integer not null, "used" integer not null default '0', "is_active" tinyint(1) not null default '1', "created_at" datetime null, "updated_at" datetime null, foreign key("user_id") references "users"("id"), foreign key("plan_id") references "plans"("id"));
CREATE TABLE IF NOT EXISTS "games" ("id" integer not null primary key autoincrement, "user_id" integer not null, "plan_id" integer not null, "category_id" integer not null, "session_token" varchar not null, "level" varchar null default 'easy', "start_time" datetime null, "expected_end_time" datetime null, "state" varchar check ("state" in ('PENDING', 'ONGOING', 'PAUSED', 'COMPLETED')) not null, "end_time" datetime null, "correct_count" integer null default '0', "wrong_count" integer null default '0', "total_count" integer null default '0', "points_gained" integer null default '0', "amount_gained" numeric null default '0', "is_winning" tinyint(1) null default '0', "payment_reference" numeric null, "created_at" datetime null, "updated_at" datetime null, "duration" integer null default '0', "live_id" integer not null default '0', foreign key("user_id") references "users"("id"), foreign key("plan_id") references "plans"("id"), foreign key("category_id") references "categories"("id"));
CREATE UNIQUE INDEX "games_session_token_unique" on "games" ("session_token");
CREATE INDEX "games_points_gained_index" on "games" ("points_gained");
CREATE TABLE IF NOT EXISTS "game_questions" ("id" integer not null primary key autoincrement, "game_id" integer not null, "question_id" integer not null, "option_id" integer null, "is_correct" tinyint(1) not null default '0', "created_at" datetime null, "updated_at" datetime null, foreign key("game_id") references "games"("id"), foreign key("option_id") references "options"("id"), foreign key("question_id") references "questions"("id"));
CREATE TABLE IF NOT EXISTS "settings" ("id" integer not null primary key autoincrement, "created_at" datetime null, "updated_at" datetime null);
CREATE INDEX "games_duration_index" on "games" ("duration");
CREATE UNIQUE INDEX "users_password_token_unique" on "users" ("password_token");
CREATE TABLE IF NOT EXISTS "vouchers" ("id" integer not null primary key autoincrement, "code" varchar not null, "expire" datetime not null, "count" integer not null default '1', "unit" numeric not null, "type" varchar not null default 'cash', "created_at" datetime null, "updated_at" datetime null);
CREATE UNIQUE INDEX "vouchers_code_unique" on "vouchers" ("code");
CREATE TABLE IF NOT EXISTS "user_vouchers" ("id" integer not null primary key autoincrement, "user_id" integer not null, "voucher_id" integer not null, "created_at" datetime null, "updated_at" datetime null, foreign key("user_id") references "users"("id"), foreign key("voucher_id") references "vouchers"("id"));
CREATE TABLE wallets (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, balance NUMERIC(10, 0) DEFAULT '0' NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, account1 NUMERIC(10, 0) DEFAULT '0' NOT NULL, account2 NUMERIC(10, 0) DEFAULT '0' NOT NULL);
CREATE TABLE wallet_transactions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, wallet_id INTEGER NOT NULL, transaction_type VARCHAR(255) NOT NULL COLLATE BINARY, amount NUMERIC(10, 0) NOT NULL, balance NUMERIC(10, 0) DEFAULT '0' NOT NULL, description VARCHAR(255) DEFAULT NULL COLLATE BINARY, reference VARCHAR(255) NOT NULL COLLATE BINARY, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, wallet_kind VARCHAR(255) DEFAULT NULL COLLATE BINARY);
CREATE UNIQUE INDEX wallet_transactions_reference_unique ON wallet_transactions (reference);
INSERT INTO migrations VALUES(1,'2014_10_12_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO migrations VALUES(3,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO migrations VALUES(4,'2019_11_02_140243_create_profiles_table',1);
INSERT INTO migrations VALUES(5,'2019_11_02_140807_create_categories_table',1);
INSERT INTO migrations VALUES(6,'2019_11_02_140930_create_questions_table',1);
INSERT INTO migrations VALUES(7,'2019_11_02_140953_create_options_table',1);
INSERT INTO migrations VALUES(8,'2019_11_02_141020_create_plans_table',1);
INSERT INTO migrations VALUES(9,'2019_11_02_141810_create_user_plan_table',1);
INSERT INTO migrations VALUES(10,'2019_11_02_142034_create_wallets_table',1);
INSERT INTO migrations VALUES(11,'2019_11_02_142101_create_wallet_transactions_table',1);
INSERT INTO migrations VALUES(12,'2019_11_02_142126_create_games_table',1);
INSERT INTO migrations VALUES(13,'2019_11_02_142336_create_game_questions_table',1);
INSERT INTO migrations VALUES(14,'2019_11_02_154005_create_settings_table',1);
INSERT INTO migrations VALUES(15,'2020_01_21_170554_add_duration_to_games',1);
INSERT INTO migrations VALUES(16,'2020_02_07_083316_add_live_column_games_table',1);
INSERT INTO migrations VALUES(17,'2020_03_02_190547_add_password_token_and_expiry_to_user_table',1);
INSERT INTO migrations VALUES(18,'2020_03_07_112748_add_password_token_and_expiry_to_pasword_reset_table',1);
INSERT INTO migrations VALUES(19,'2020_06_16_003735_create_vouchers_table',1);
INSERT INTO migrations VALUES(20,'2020_06_26_063800_create_user_vouchers_table',1);
INSERT INTO migrations VALUES(21,'2020_07_10_164543_add_referral_code_to_profiles_table',1);
INSERT INTO migrations VALUES(22,'2020_08_17_120058_rename_bonus_and_cash_columns_on_wallets',1);
INSERT INTO migrations VALUES(23,'2020_08_17_130609_rename_enum_wallet_type',1);
INSERT INTO migrations VALUES(24,'2020_08_28_091001_add_referrer_column',1);
INSERT INTO migrations VALUES(25,'2020_09_18_093021_alter_column_wallet_type_wallet_transactions',1);
