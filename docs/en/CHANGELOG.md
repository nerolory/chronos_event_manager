# Changelog

All notable changes to Chronos Event Manager will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-06-04

### Added

#### Core Features
- **Kinetic Clock Engine**
  - Animated clock display using kinetic typography
  - 28x8 grid of individual clock faces forming digits
  - Real-time clock hand animations
  - Multiple time zone support with timezone selector
  - Gradient effects on inactive clocks
  - Volume/depth effects with box-shadows
  - Border clock styling with table-like appearance
  - Smooth cubic-bezier animations

- **Calendar System**
  - Monthly calendar view with 42-day grid
  - Drag-and-drop event scheduling
  - Multi-day event support with visual indicators
  - Event color coding with automatic text contrast
  - Weekend and holiday highlighting
  - Past date visual distinction
  - Event expansion/collapse (3 events collapsed, unlimited expanded)
  - Side panel for event details
  - Upcoming events list

- **Event Management**
  - Create, edit, and delete events
  - Recurring events (daily, weekly, monthly)
  - Event categorization with custom colors
  - Time zone aware event scheduling
  - Event duration preservation during drag-and-drop
  - Batch operations support

- **UI/UX Features**
  - Toast notification system
  - Loading skeletons for async operations
  - Responsive design
  - Modern UI with Tailwind CSS
  - BEM methodology for CSS naming
  - CSS custom properties for dynamic styling

#### Backend
- **API Service Layer**
  - Centralized API service for HTTP requests
  - Events API service for event-specific operations
  - CSRF token handling
  - Centralized error handling

- **Data Layer**
  - Pinia store for state management
  - Computed properties for derived state
  - Reactive event filtering and sorting

- **Utilities**
  - Date utilities for timezone-safe operations
  - Date formatting and parsing
  - Time preservation during date changes
  - Past date detection

#### Code Quality
- **Linting and Formatting**
  - ESLint configuration for JavaScript/Vue
  - Stylelint configuration for CSS/SCSS
  - JSDoc documentation for all composables and services
  - Modern ES6+ patterns throughout

- **Architecture**
  - Vue 3 Composition API
  - Composable pattern for reusable logic
  - Atomic component structure
  - Separation of concerns (API, state, UI)

#### Development Tools
- **Build System**
  - Vite for fast development and building
  - Laravel Vite plugin for Laravel integration
  - SCSS support with embedded Sass
  - Tailwind CSS v4 for utility styling

- **Package Management**
  - npm scripts for linting (PHP, JS, CSS)
  - Automated code quality checks
  - Docker support for containerized development

### Changed

#### Refactoring
- Extracted API requests into dedicated service layer
- Moved business logic into Vue composables
- Split complex components into atomic blocks
- Replaced inline styles with CSS custom properties
- Migrated to modern ES6+ patterns (arrow functions, destructuring, template literals)
- Removed unused variables and imports
- Improved code organization and maintainability

#### Styling
- Updated button styling for "+N" event expansion button
- Implemented contrast-based text coloring for events
- Added gradient effects to inactive kinetic clocks
- Enhanced border clock styling with volume effects
- Improved shadow handling to prevent overlap
- Modernized color notation (rgba to hex with alpha)

### Fixed

- **Drag and Drop**
  - Fixed event date update during drag-and-drop
  - Resolved timezone issues in date calculations
  - Fixed event duration preservation

- **UI Issues**
  - Fixed text contrast on light event backgrounds
  - Resolved button arrow visibility issues
  - Fixed event expansion/collapse functionality

- **Code Quality**
  - Removed all console.log statements
  - Fixed ESLint warnings (unused variables, parameters)
  - Fixed Stylelint issues (color notation, specificity)
  - Resolved Vue component naming issues

### Security

- CSRF token protection for all API requests
- Input validation for event data
- SQL injection prevention through parameterized queries

### Performance

- Optimized event filtering with computed properties
- Lazy loading of calendar data
- Efficient DOM updates with Vue reactivity
- CSS transitions for smooth animations

### Documentation

- Complete installation guide (Docker and native Linux)
- Comprehensive user guide
- API documentation via JSDoc
- Code comments and inline documentation

---

## Installation Notes

### System Requirements
- PHP 8.1+
- Node.js 18+
- MySQL 8.0+ or PostgreSQL 13+
- Docker 20.10+ (for Docker installation)

### Upgrade Instructions

If upgrading from a previous version:

1. Backup your database
2. Run `composer install`
3. Run `npm install`
4. Run `php artisan migrate`
5. Run `npm run build`
6. Clear application cache: `php artisan cache:clear`

---

## License

This project is licensed under the Apache 2.0 License.

**Copyright Notice**: All rights reserved. This code cannot be sold or claimed as your own. Integration services (even paid) are permitted and do not violate copyright.

---

## Support

For support, please refer to the documentation in the `docs/` directory or contact the development team.
