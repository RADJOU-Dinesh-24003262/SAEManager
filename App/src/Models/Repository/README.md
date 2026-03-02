# Repository Architecture Guide

This project follows a precise and strict architectural pattern for Repositories in the Infrastructure layer. Because database structures can be inherently complex (table inheritance, composite keys), we do not force all Repositories to follow the exact same inheritance pattern.

Instead, every Repository falls into one of three **Types**. Understanding these types is crucial for properly maintaining and expanding the codebase.

## Type 1: Single Table (Standard)
These repositories map directly to a single table with a single primary key.
- **Rule:** MUST extend `BaseRepository` and use the standard CRUD methods inherited from it.
- **Examples:** `PdoUserRepository`, `PdoSAEGroupRepository`, `PdoToDoListRepository`.

## Type 2: Inherited / Polymorphic
These repositories manage entities that are involved in database inheritance structures (e.g., `students`, `professors`, `clients` all inheriting from `users` and requiring SQL `JOIN`s to fetch complete entity data).
- **Rule:** MUST NOT extend `BaseRepository`, because `BaseRepository` assumes a simple mapping to a single table. Doing so would break inheritance hydration. They use composition (e.g., injecting `PdoUserRepository` internally) and implement their specific Interface directly.
- **Examples:** `PdoStudentRepository`, `PdoProfessorRepository`, `PdoClientRepository`.

## Type 3: Association (Composite Keys)
These manage many-to-many relationship tables that do not have a single standard integer Primary Key.
- **Rule:** MUST NOT implement `RepositoryInterface` nor extend `BaseRepository`. They only implement their domain-specific Interface, exposing only necessary association methods (like `assignStudentToGroup()`).
- **Examples:** `PdoParticipatedInRepository`.

---

*Note: All repositories strictly adhere to their designated Interfaces defined in the `UseCase` layer, regardless of their Type.*
