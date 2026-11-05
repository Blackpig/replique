# Changelog

All notable changes to `replique` will be documented in this file.

## v2.0.1 - 2026-11-05

### Fixed

- Adding `RepliquePlugin` to more than one panel threw "A Tome with id 'replique' is already registered". The Grimoire documentation Tome is now registered once in `RepliqueServiceProvider` instead of once per panel in the plugin.

## 1.0.0 - 202X-XX-XX

- initial release
