# MKWA Fitness Plugin

## Description
The MKWA Fitness Plugin adds gamification features to your fitness community, including points, badges, daily quests, a workout buddy finder, leaderboard, rewards store, and user registration/profile management.

## Features
- Points and badges system to reward user activities.
- Daily quests to engage users and promote consistency.
- Workout buddy finder to match users with similar goals and availability.
- Leaderboard showcasing top performers (`overall`, `monthly`, `weekly`).
- Rewards store for redeeming points.
- Custom user registration and profile management.

---

## Installation
1. Upload the plugin to your WordPress `/wp-content/plugins/` directory or install via the WordPress admin dashboard.
2. Activate the plugin through the **Plugins** menu.
3. Ensure the necessary tables are created during activation (check debug logs if issues occur).

---

## Shortcodes
Use the following shortcodes in your pages/posts:
- `[mkwa_daily_quests]`: Display the daily quests.
- `[mkwa_buddy_finder]`: Display the workout buddy finder.
- `[mkwa_leaderboard]`: Display the leaderboard.
- `[mkwa_dashboard]`: Display the user dashboard.
- `[mkwa_rewards_store]`: Display the rewards store.
- `[mkwa_registration_form]`: Display the registration form.
- `[mkwa_badge_showcase]`: Display user badges.

---

## Admin Features
- **Badge Management**: Award badges manually or automatically based on user actions.
- **Daily Quests Management**: Add and edit daily quests.
- **Rewards Store Management**: Manage available rewards and stock.
- **Leaderboard Management**: Reset and update leaderboards.

---

## Development Notes
### Database Tables
The plugin creates the following tables:
1. `mkwa_points`: Logs points awarded to users.
2. `mkwa_leaderboard`: Tracks leaderboard data.
3. `mkwa_daily_quests`: Manages daily quest assignments.
4. `mkwa_buddy_finder`: Stores buddy finder preferences.
5. `mkwa_rewards`: Stores reward data.
6. `mkwa_rewards_log`: Logs reward redemptions.

### Hooks and Filters
- `mkwa_reset_streaks_daily`: Resets daily streaks.
- `mkwa_clean_buddy_finder`: Cleans up inactive buddy finder entries.
- `mkwa_reset_leaderboard_monthly`: Resets monthly leaderboard data.

---

## Testing Checklist
- [ ] Plugin activation works without errors.
- [ ] Database tables are created correctly.
- [ ] All shortcodes render as expected.
- [ ] Admin features are functional.
- [ ] AJAX actions respond correctly to valid/invalid inputs.
- [ ] Security checks (nonce validation, sanitization) are in place.
- [ ] Gamification features (points, badges, quests, leaderboards) work as intended.

---

## Changelog
### Version 2.2
- Improved database table structure and creation.
- Added security checks for all user inputs.
- Optimized leaderboard queries for performance.

---

## Support
For support or feature requests, please contact the MKWA Fitness team.
