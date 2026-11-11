# Security and sensitive data handling

This source repository contains legal-software code and must not be treated as a safe place for live customer information or credentials.

## Important historic exposure notice

Tracked environment files or signing keys were previously found in this repository. Removing them from the latest branch **does not delete the earlier Git objects, forks, caches or anyone's local clone**.

If any real application key, API token, password, database credential or Android signing identity was present:
1. Identify all affected systems without sharing secret values in an issue.
2. Rotate or revoke live credentials using the provider's official procedure.
3. Investigate repository history and coordinate any history rewrite with collaborators and deploy automation.
4. Confirm secret files are excluded from future commits and that deployment uses out-of-band configuration.
5. For Android signing keys, follow Play App Signing / upload-key recovery rules rather than assuming arbitrary replacement is safe.

## Reporting

**Do not publish vulnerabilities, credentials or client records in a public GitHub issue.** Contact the owner through an existing private professional channel or use private vulnerability reporting if enabled for the repository.

## Release principle

A green CI check is a basic hygiene signal, not a security certification. Validate authorization, dependency versions, backups, logs and user-data access separately before production.
