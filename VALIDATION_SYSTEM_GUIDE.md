# Syllabus Validation & Progress System

## Overview

The syllabus validation system provides:
1. **Progress Bar** - Visual indicator showing completion percentage of required fields
2. **Submit Button Lock** - Submit button is disabled until all required fields are complete
3. **Field Validation** - Checks that required fields are not empty
4. **Extensibility** - Easy to add more required fields as you build features

## Files Involved

### Core Files
- `resources/js/faculty/syllabus-validation.js` - Main validation engine
- `resources/views/faculty/syllabus/syllabus.blade.php` - Blade template with UI
- `resources/views/faculty/syllabus/partials/course-info.blade.php` - Example partial with registered fields

### Helper Files
- `resources/js/faculty/syllabus-validation-helpers.js` - Helper utilities

## Current Required Fields

Currently, the following fields are required for submission:

### Course Info Partial
- `course_description` - Course Rationale and Description

## How It Works

### Progress Bar
The progress bar displays at the top of the page (fixed position):
- **Height**: 6px + 24px text
- **Colors**: 
  - Red (danger) when < 50% complete
  - Yellow (warning) when 50-99% complete
  - Green (success) when 100% complete
- **Text**: Shows "X/Y required fields completed"

### Submit Button
The submit button (`#syllabusSubmitBtn`) is:
- **Disabled** when validation fails (any required field is empty)
- **Enabled** when all required fields have content
- **Shows tooltip**: "Complete all required fields before submitting" when disabled

### Validation Logic
1. A field is considered "complete" if its value (trimmed) is not empty
2. Validation updates automatically whenever a field changes
3. Debounced with 300ms delay to avoid performance issues

## Adding More Required Fields

### Method 1: During Partial Load (Recommended)

In your partial blade file, add this at the end of the `@push('scripts')` section:

```php
@push('scripts')
<script>
  (function(){
    // Your existing partial code...

    // Register validation fields for this partial
    function registerValidationFields(){
      if (typeof window.addRequiredField === 'function') {
        window.addRequiredField('course_info', 'field_name_1', 'Field Label 1');
        window.addRequiredField('course_info', 'field_name_2', 'Field Label 2');
        console.log('Validation fields registered');
      } else {
        setTimeout(registerValidationFields, 500);
      }
    }
    registerValidationFields();
  })();
</script>
@endpush
```

### Method 2: From JavaScript (Any Time)

Call this function from any JavaScript module after the validation system is initialized:

```javascript
window.addRequiredField('partial_name', 'field_name', 'Field Display Label');
```

Example:
```javascript
window.addRequiredField('course_info', 'course_title', 'Course Title');
window.addRequiredField('course_info', 'semester', 'Semester/Year');
```

### Method 3: From Inline Script in Template

Add directly in your view:

```php
<script>
  document.addEventListener('DOMContentLoaded', function(){
    if (typeof window.addRequiredField === 'function') {
      window.addRequiredField('course_info', 'course_title', 'Course Title');
      window.addRequiredField('course_policies', 'grading_scale', 'Grading Scale');
    }
  });
</script>
```

## API Reference

### `window.addRequiredField(partial, fieldName, fieldLabel)`
Registers a new required field for validation.

**Parameters:**
- `partial` (string) - Partial category (e.g., 'course_info', 'course_policies', 'assessment')
- `fieldName` (string) - HTML form field name attribute
- `fieldLabel` (string) - Display name for the field

**Example:**
```javascript
window.addRequiredField('course_info', 'course_description', 'Course Rationale and Description');
```

### `window.getSyllabusValidationStatus()`
Returns current validation status.

**Returns:**
```javascript
{
  isValid: boolean,           // true if all required fields are complete
  completed: number,          // number of complete fields
  total: number,              // total required fields
  percentage: number          // completion percentage (0-100)
}
```

**Example:**
```javascript
const status = window.getSyllabusValidationStatus();
console.log(`${status.completed}/${status.total} fields complete (${status.percentage}%)`);
if (status.isValid) {
  // All fields complete
}
```

### `window.isSyllabusValid()`
Quick check if syllabus is valid for submission.

**Returns:** boolean

**Example:**
```javascript
if (window.isSyllabusValid()) {
  // Ready to submit
} else {
  console.log('Please complete all required fields');
}
```

## Field Categories (Partials)

When adding required fields, use these partial names:

- `course_info` - Course Information section
- `course_policies` - Course Policies section  
- `assessment` - Assessment & Grading
- `ilo` - Intended Learning Outcomes
- `resources` - Course Resources/Textbooks
- `tla` - Teaching & Learning Activities
- `cdio` - CDIO Framework
- `sdg` - SDG Mappings

*(Add more as needed)*

## Styling & Customization

### Progress Bar Colors

Change these CSS classes to customize colors:

```css
/* In syllabus.blade.php @push('styles') */

/* Red danger state (< 50%) */
#syllabus-progress-bar.bg-danger { background-color: #dc3545 !important; }

/* Yellow warning state (50-99%) */
#syllabus-progress-bar.bg-warning { background-color: #ffc107 !important; }

/* Green success state (100%) */
#syllabus-progress-bar.bg-success { background-color: #28a745 !important; }
```

### Progress Bar Position

The progress bar is fixed at the top. Adjust these if needed:

```css
.syllabus-progress-bar-container {
  top: 0;              /* Change to move down */
  z-index: 1050;       /* Change to adjust layering */
  background: #fff;    /* Change to adjust background */
}
```

## Validation Triggers

Validation updates automatically when:
- User types in a field (`input` event)
- User changes field value (`change` event)
- New required fields are registered via `addRequiredField()`
- Page loads

All updates are debounced with a 300ms delay to prevent excessive recalculation.

## Testing the System

1. **Open Syllabus Edit Page** - Progress bar should show at top
2. **Check Progress Bar** - Should show "0/1 required fields completed" (or more if you added fields)
3. **Check Submit Button** - Should be disabled
4. **Fill course_description** - Type something in the "Course Rationale and Description" field
5. **Observe Changes**:
   - Progress bar should fill to 100% (green)
   - Text should show "1/1 required fields completed"
   - Submit button should become enabled
6. **Try to Submit** - Should now open the submit modal
7. **Clear the Field** - Submit button should disable again

## Debugging

Enable debug logging by checking browser console:

```javascript
// Check current validation state
window.getSyllabusValidationStatus()

// Check if valid
window.isSyllabusValid()

// Check registered fields (internal)
console.log(window._syllabusValidationReady)
```

## Notes for Future Development

- **Partial Status**: Currently only `course_info.course_description` is required
- **Expandable**: Add more fields incrementally using `addRequiredField()`
- **Performance**: Validation is debounced; adding many fields is safe
- **UX**: Consider adding tooltips to required fields to help users
- **Backend**: Server-side validation should also be implemented in the controller

## Example: Adding Multiple Sections

```javascript
// From any JS module or inline script
window.addEventListener('load', function(){
  // Course Info
  window.addRequiredField('course_info', 'course_title', 'Course Title');
  window.addRequiredField('course_info', 'course_code', 'Course Code');
  window.addRequiredField('course_info', 'course_description', 'Course Description');
  
  // Learning Outcomes
  window.addRequiredField('ilo', 'ilo_list', 'Learning Outcomes');
  
  // Assessment
  window.addRequiredField('assessment', 'grading_scale', 'Grading Scale');
  window.addRequiredField('assessment', 'course_requirements', 'Course Requirements');
});
```

## Troubleshooting

### Progress Bar Not Showing
- Check browser console for errors
- Ensure `resources/js/faculty/syllabus-validation.js` is loaded
- Verify `@vite('resources/js/faculty/syllabus-validation.js')` is in syllabus.blade.php

### Submit Button Not Disabling
- Verify progress bar shows (indicates validation system is running)
- Check that required fields are being registered
- Look for JavaScript errors in console

### Fields Not Updating
- Ensure form field has proper `name` attribute
- Check field is visible and not in a hidden element
- Verify field is included before validation system initializes

### Validation Triggers Inconsistently  
- Check for multiple event listeners on same field
- Look for other JavaScript modifying form state
- Increase debounce delay in `syllabus-validation.js` if needed
