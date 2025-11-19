# General Information Department Support

## Overview
Added department-specific support to the `general_information` table, allowing different departments to have their own **course policy defaults** while maintaining university-wide fallbacks.

**Note**: Mission and vision remain university-wide (same for all departments). Only course policies (policy, exams, dishonesty, dropping, other) support department-specific overrides.

## Changes Made

### 1. Database Migration
**File**: `database/migrations/2025_11_19_000000_add_department_id_to_general_information_table.php`

- Added nullable `department_id` foreign key to `general_information` table
- Added unique constraint: `(section, department_id)` must be unique
- NULL `department_id` = university-wide default (fallback)
- Non-NULL `department_id` = department-specific override

### 2. Model Updates
**File**: `app/Models/GeneralInformation.php`

- Added `department_id` to `$fillable`
- Added `department()` relationship
- Added static `getContent(string $section, ?int $departmentId)` method
  - **Lookup hierarchy**: Department-specific → University default
  - Returns empty string if neither found

### 3. Controller Updates

#### SyllabusMissionVisionController
**File**: `app/Http/Controllers/Faculty/Syllabus/SyllabusMissionVisionController.php`

- Mission and vision use **university-wide defaults only** (no department-specific overrides)
- Queries for mission/vision explicitly filter `whereNull('department_id')`

#### SyllabusController
**File**: `app/Http/Controllers/Faculty/Syllabus/SyllabusController.php`

- Updated **course policies** seeding to use department-aware lookup
- Uses `GeneralInformation::getContent($section, $departmentId)` for policy sections only
- Gets department ID from `$syllabus->course->department_id`

## How It Works

### Lookup Hierarchy
1. **Department-specific**: If `department_id` is provided, look for entry with matching `section` and `department_id`
2. **University default**: If not found (or no department_id), look for entry with matching `section` and `NULL department_id`
3. **Empty fallback**: If neither found, return empty string

### Example Scenarios

#### Scenario 1: University-wide only (mission/vision)
```
general_information:
  - id: 1, department_id: NULL, section: 'mission', content: 'University mission...'
  
Query: GeneralInformation::where('section', 'mission')->whereNull('department_id')->first()
Result: 'University mission...' (always uses university-wide for mission/vision)
```

#### Scenario 2: Department override exists (course policies only)
```
general_information:
  - id: 3, department_id: NULL, section: 'policy', content: 'University class policy...'
  - id: 8, department_id: 5, section: 'policy', content: 'CS Department class policy...'
  
Query: GeneralInformation::getContent('policy', 5)
Result: 'CS Department class policy...' (department-specific found first)
```

#### Scenario 3: Different department (course policies)
```
general_information:
  - id: 3, department_id: NULL, section: 'policy', content: 'University class policy...'
  - id: 8, department_id: 5, section: 'policy', content: 'CS Department class policy...'
  
Query: GeneralInformation::getContent('policy', 3)
Result: 'University class policy...' (department 3 has no override, falls back to university)
```

## Impact on Existing Data

✅ **All existing data is preserved** as university-wide defaults (department_id = NULL)
✅ **No data migration needed** - existing behavior continues to work
✅ **Backward compatible** - calling without department_id returns university defaults

## Usage Examples

### Creating Department-Specific Entry (Course Policies Only)
```php
// Department-specific class policy for CS department
GeneralInformation::create([
    'section' => 'policy',
    'department_id' => 5,
    'content' => 'Computer Science Department class policy...'
]);

// Note: Mission and vision should NOT have department_id
// They remain university-wide only
```

### Retrieving Content
```php
// Mission and vision (university-wide only)
$mission = GeneralInformation::where('section', 'mission')->whereNull('department_id')->first()?->content ?? '';
$vision = GeneralInformation::where('section', 'vision')->whereNull('department_id')->first()?->content ?? '';

// Course policies (department-aware with fallback)
$policy = GeneralInformation::getContent('policy', 5);  // Department 5 or university fallback
$exams = GeneralInformation::getContent('exams', null); // University default only
```

### In Controllers
```php
// Mission/Vision: Always university-wide
$mission = GeneralInformation::where('section', 'mission')->whereNull('department_id')->first()?->content ?? '';

// Course Policies: Department-aware
$departmentId = $syllabus->course->department_id ?? null;
$policy = GeneralInformation::getContent('policy', $departmentId);
```

## Next Steps

To add department-specific overrides:
1. Create UI in admin/superadmin panel to manage department-specific general information
2. Add ability to view/edit both university and department-specific entries
3. Add indicators showing which departments have overrides
4. Consider adding a "reset to university default" option for departments

## Testing

All existing functionality continues to work because:
- Existing entries have `department_id = NULL`
- The lookup method checks department-specific first, then falls back to NULL
- If no department_id provided, only university defaults are returned
