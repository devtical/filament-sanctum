# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com)
and this project adheres to [Semantic Versioning](http://semver.org).

## v1.1.0 - 2026-07-04

### Summary

This release extends **Filament Sanctum** with Filament 5 and Laravel 13 support, a richer token management UI, and several bug fixes from real-world usage.

### Added

- **Filament 5** and **Laravel 13** support
- **Token expiration** when creating tokens — preset options (7, 30, 60, 90 days), custom date, or no expiration
- **Token details modal** — view abilities, expiration, last used, and created timestamps
- **Per-row revoke action** for individual token deletion
- **Authorization gate** — restrict Sanctum page access via configurable Laravel gate
- **Configurable navigation slug** — customize the page URL from config
- **Expanded config options** — `default_expiration_days`, `expiration_presets`, and `authorization` settings
- **Translations** for token details and expiration UI (EN, ID)
- **Test coverage** — feature tests for token create/revoke, URL resolution, gate access, and plugin registration
- **CI improvements** — Laravel 12/13 test matrix and Pint lint job

### Fixed

- **Duplicate panel path in user menu URL** (`/admin/admin/sanctum`) — URLs now resolve correctly via centralized `Sanctum::getUrl()`
- **Abilities column display** — fixed repeated "None" / "5 abilities" text caused by Filament iterating array state per item
- **Filament 5 compatibility** — migrated `MenuItem` to `Action`, replaced deprecated `TagsColumn` with badge-style `TextColumn`

**Full Changelog**: https://github.com/devtical/filament-sanctum/compare/v1.0.0...v1.1.0

## v1.0.0 - 2025-09-21

### What's Changed

* Bump tailwindcss from 3.4.17 to 4.0.4 by @dependabot[bot] in https://github.com/devtical/filament-sanctum/pull/15
* feat: Add support for Sanctum v4 and Filament v3 by @aphoe in https://github.com/devtical/filament-sanctum/pull/16
* Support Filament v4 Compatibility by @Kristories in https://github.com/devtical/filament-sanctum/pull/17
* Filament v4 README by @Kristories in https://github.com/devtical/filament-sanctum/pull/18

### New Contributors

* @dependabot[bot] made their first contribution in https://github.com/devtical/filament-sanctum/pull/15
* @aphoe made their first contribution in https://github.com/devtical/filament-sanctum/pull/16

**Full Changelog**: https://github.com/devtical/filament-sanctum/compare/v0.0.6...v1.0.0

## Unreleased
