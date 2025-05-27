# Route Naming Standardization Plan

## Current Issues

- Inconsistent naming patterns (app_training_program vs app_exercise_library)
- Mixed dashboard and direct routes
- Some routes have redundant prefixes
- API routes not consistently named

## New Naming Convention

### Pattern: `app_{module}_{action}_{type?}`

### Modules:

- `dashboard` - Main dashboard pages
- `programs` - Training programs (shortened from training_program)
- `exercises` - Exercise library
- `workouts` - Workout logging and tracking
- `progress` - Progress tracking and reports
- `blog` - Blog/community features
- `goals` - Fitness goals
- `profile` - User profile
- `admin` - Admin panel

### Actions:

- `index` - List/main page
- `show` - View single item
- `create` - Create new item
- `edit` - Edit existing item
- `delete` - Delete item
- `api` - API endpoints

### Types (optional):

- `ajax` - AJAX endpoints
- `modal` - Modal content
- `data` - Data endpoints

## Proposed Route Changes

### Dashboard Routes ✅ (Already good)

- `app_dashboard_overview` → Keep
- `app_dashboard_today` → Keep
- `app_dashboard_programs` → Keep
- `app_dashboard_exercise_library` → Keep

### Training Program Routes (Rename)

- `app_training_program_index` → `app_programs_index`
- `app_training_program_exercises` → `app_programs_exercises_api`
- `app_training_program_create` → `app_programs_create`
- `app_training_program_edit` → `app_programs_edit`
- `app_training_program_delete` → `app_programs_delete`
- `app_training_program_toggle_status` → `app_programs_toggle_status`
- `app_training_program_api_user_programs` → `app_programs_api`
- `app_training_program_add_exercise` → `app_programs_add_exercise`

### Exercise Library Routes (Rename)

- `app_exercise_library_index` → `app_exercises_index`
- `app_exercise_library_api` → `app_exercises_api`
- `app_exercise_library_detail` → `app_exercises_show`
- `app_exercise_library_muscle_groups` → `app_exercises_muscle_groups_api`
- `app_exercise_library_create_custom` → `app_exercises_create`
- `app_exercise_library_favorites` → `app_exercises_favorites`

### Blog Routes ✅ (Already good)

- Keep all `app_blog_*` routes as they are

### Progress Routes ✅ (Already good)

- Keep all `app_progress_*` routes as they are

### Goals Routes ✅ (Already good)

- Keep all `app_goals_*` routes as they are

### Profile Routes ✅ (Already good)

- Keep all `app_profile_*` routes as they are

### Admin Routes ✅ (Already good)

- Keep all `app_admin_*` routes as they are

## Implementation Plan

1. **Update Controllers** - Change route names in annotations
2. **Update Templates** - Update all path() calls
3. **Update Stimulus Controllers** - Update URL values
4. **Test All Links** - Ensure no broken links
5. **Update Documentation** - Update route documentation

## Benefits

- Consistent naming across all modules
- Shorter, more readable route names
- Clear separation between regular and API routes
- Easier to remember and maintain
- Better organization for future development
