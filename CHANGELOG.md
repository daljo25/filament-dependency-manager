# Changelog

All notable changes to `filament-dependency-manager` will be documented in this file.

## v5.1.0 - 2026-10-13

### What's new

#### Bun support (closes #4)

The NPM manager now supports `bun` via `DEPENDENCY_MANAGER_NPM_CLIENT=bun`. Since `bun outdated` has no JSON output, the plugin runs `bunx npm outdated --json` under the hood, with a fallback that parses the native `bun outdated` table. The copy-command action suggests `bun add <pkg>@<version>`.

#### Security hardening

- All GitHub Actions pinned to full commit SHAs (Dependabot keeps them updated).
- Dependabot `cooldown` configured (7d default / 14d major / 7d minor / 3d patch) and `composer` ecosystem added.

#### Fixes

- Dependency type (dependencies vs devDependencies) now resolved from `package.json`.
- JSON parsing tolerates non-JSON noise on stdout.

## 1.0.0 - 202X-XX-XX

- initial release
