# User Guide

This guide explains how to use Chronos Event Manager, a comprehensive event management system featuring a kinetic clock display, calendar interface, and batch programming capabilities.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Getting Started](#getting-started)
- [Using the Calendar](#using-the-calendar)
- [Managing Events](#managing-events)
- [Kinetic Clock](#kinetic-clock)
- [Batch Operations](#batch-operations)
- [Keyboard Shortcuts](#keyboard-shortcuts)
- [Tips and Tricks](#tips-and-tricks)

---

## Overview

Chronos Event Manager is designed to help you organize and manage events efficiently. The application consists of three main components:

1. **Kinetic Clock** - An animated clock display that shows the current time using kinetic typography
2. **Calendar** - A full-featured calendar for viewing and managing events
3. **Batch Programming** - Tools for performing bulk operations on events

---

## Features

### Calendar Features
- Monthly, weekly, and daily views
- Drag-and-drop event scheduling
- Multi-day event support
- Recurring events
- Time zone support
- Event categorization with color coding
- Export and import events

### Kinetic Clock Features
- Real-time animated clock display
- Multiple time zone support
- Smooth animations
- Customizable display

### Batch Operations
- Bulk event creation
- Bulk event editing
- Bulk event deletion
- Export events to CSV
- Import events from CSV

---

## Getting Started

### First Login

1. Navigate to the application URL
2. Enter your credentials
3. Click "Login"

### Dashboard Overview

The main dashboard displays:
- **Kinetic Clock** - Top section showing current time
- **Calendar** - Main calendar view
- **Side Panel** - Event details and upcoming events
- **Controls** - Navigation and action buttons

---

## Using the Calendar

### Navigation

- **Previous/Next Month**: Use the arrow buttons in the header
- **Today**: Click "Today" to jump to the current month
- **Date Selection**: Click on any day to view its events

### Creating Events

1. Click on a day in the calendar
2. Click the "+ Add Event" button in the side panel
3. Fill in the event details:
   - **Title**: Event name
   - **Start Date/Time**: When the event begins
   - **End Date/Time**: When the event ends
   - **Color**: Choose a color for the event
   - **Recurrence**: Set if the event repeats (optional)
4. Click "Save"

### Editing Events

1. Click on an event in the calendar or side panel
2. Click the edit (✎) button
3. Modify the event details
4. Click "Save"

### Deleting Events

1. Click on an event in the calendar or side panel
2. Click the delete (✕) button
3. Confirm the deletion

### Drag and Drop

To reschedule an event:
1. Click and hold on an event
2. Drag it to a new day
3. Release the mouse button

The event will be moved to the new date while preserving its time.

### Viewing More Events

If a day has more events than can be displayed:
1. Click the "+N" button at the bottom of the day
2. The day will expand to show all events
3. Click the button again to collapse

---

## Managing Events

### Recurring Events

Chronos supports several recurrence patterns:

- **Daily**: Event repeats every day
- **Weekly**: Event repeats on specific days of the week
- **Monthly**: Event repeats on a specific day of the month

To set recurrence:
1. When creating/editing an event, select a recurrence type
2. Configure the recurrence options:
   - For weekly: select the days of the week
   - For monthly: specify the day of the month
3. Set the end condition:
   - Never
   - After N occurrences
   - Until a specific date

### Event Colors

Events can be color-coded for better organization:
- Select a color when creating/editing an event
- Colors help visually categorize events
- The system automatically adjusts text color for readability

### Time Zones

The kinetic clock supports multiple time zones:
- Click on a time zone button to switch
- The clock updates to show the selected time zone
- Events remain in their original time zone

---

## Kinetic Clock

The kinetic clock displays the current time using animated clock faces arranged to form digits.

### Features
- **Real-time updates**: Clock updates every second
- **Smooth animations**: Clock hands animate smoothly
- **Time zone support**: Display time in different zones
- **Visual design**: Gradient effects and shadows for depth

### Using the Clock
- The clock automatically displays the current time
- Click time zone buttons at the bottom to switch zones
- The active time zone is highlighted

---

## Batch Operations

### Bulk Event Creation

1. Navigate to the "Batch Operations" section
2. Select "Create Events"
3. Upload a CSV file with event data
4. Review the events
5. Click "Import"

CSV format:
```csv
title,start,end,color
Meeting 1,2024-01-15 09:00:00,2024-01-15 10:00:00,#4f46e5
Meeting 2,2024-01-16 14:00:00,2024-01-16 15:00:00,#22c55e
```

### Bulk Event Editing

1. Select multiple events using checkboxes
2. Click "Bulk Edit"
3. Modify the fields you want to change
4. Click "Apply Changes"

### Bulk Deletion

1. Select multiple events using checkboxes
2. Click "Bulk Delete"
3. Confirm the deletion

### Export Events

1. Navigate to the "Export" section
2. Select the date range
3. Choose export format (CSV, JSON)
4. Click "Export"

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `N` | Create new event |
| `T` | Go to today |
| `←` | Previous month |
| `→` | Next month |
| `Esc` | Close modal/dialog |
| `Delete` | Delete selected event |
| `Ctrl/Cmd + S` | Save event |

---

## Tips and Tricks

### Productivity Tips
- Use drag-and-drop for quick rescheduling
- Color-code events by category (work, personal, etc.)
- Set up recurring events for regular meetings
- Use the side panel to quickly view upcoming events

### Visual Tips
- Events with light backgrounds automatically use dark text
- Events with dark backgrounds automatically use light text
- The kinetic clock shows subtle shadows for depth
- Inactive clocks have a gradient effect

### Performance Tips
- For large calendars, use the search function to find events
- Export old events to CSV and delete them from the database
- Use recurring events instead of creating individual events

---

## Troubleshooting

### Events Not Displaying
- Check that the date range is correct
- Ensure events are not hidden by filters
- Refresh the page

### Drag and Drop Not Working
- Ensure you're not dragging to a past date (if restricted)
- Try refreshing the page
- Check browser console for errors

### Kinetic Clock Not Animating
- Ensure JavaScript is enabled
- Check browser compatibility (modern browsers required)
- Try refreshing the page

---

## Support

For issues or questions:
- Check the [Installation Guide](INSTALLATION.md)
- Review the [Changelog](CHANGELOG.md) for known issues
- Contact support at support@chronos-event-manager.com

---

## License

This project is licensed under the Apache 2.0 License. See the [LICENSE](../LICENSE) file for details.

**Important**: This code cannot be sold or claimed as your own. All rights are reserved. Integration services (even paid) are permitted and do not violate copyright.
