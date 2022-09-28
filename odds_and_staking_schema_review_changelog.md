
# Changelog
All notable changes to the odds and staking schema will be documented in this file.

This will assist in keeping track of updates that need to be made in order places to reconcile with these changes.

## Changes
### odds_conditions_and_rules table
- odds_conditions_and_rules table renamed to odds_rules
- column `odds_operation` added
- column `condition` renamed to `display_name`

### game session odds table
- `game_session_odds` table created
- `odds_benefit` - denoting the odds applied to the game - is now being held in this table.

### staking odds table
- `standard_odds` table renamed to `staking_odds`
- `module` column added to staking_odds table (representing kind of game e.g exhibition, trivia)
- `odds/standard` endpoint changed to `odds/staking` [Frontend client update required]

### stakings table
- `amount` column renamed to `amount_staked`
- `amount_won` column added (data from `amount_won` column on game_sessions table should be migrated here)

### Exhibitions staking
- `odds_applied` column added (to store odds of the exhibition game session)

### Trivia staking
- `trivia_stakings` table added (to separate trivia stakings from exhibitions stakings)

