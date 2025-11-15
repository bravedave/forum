# Plan: Project Management System (GitHub Projects-style)

Building a flexible project management system with forum items as the core entity, supporting projects, assignees, labels, statuses, milestones, and item relationships. Uses the BraveDave DVC framework with its DAO/DTO abstraction layer for SQLite/MariaDB compatibility.

## Steps

1. **Generate core modules via CLI** using `vendor/bin/dvc make::crud-module` for forum_items, projects, labels, milestones, creating controllers, DAOs, DTOs, handlers, and views in `src/app/{module}/` directories

2. **Define database schema** in `dao/db/*.php` files for tables:
   - `forum_items` (id, title, description, status, priority, created, updated, created_by, assigned_to, milestone_id, due_date)
   - `projects` (id, name, description, created, updated, created_by)
   - `forum_item_projects` (forum_item_id, project_id) for many-to-many relationship
   - `labels` (id, name, color, description)
   - `forum_item_labels` (forum_item_id, label_id) for many-to-many
   - `forum_item_assignees` (forum_item_id, user_id) for many-to-many assignments
   - `forum_item_relations` (from_item_id, to_item_id, relation_type) for item-to-item links
   - `milestones` (id, name, description, due_date, status)
   - Add proper indexes on foreign keys and status fields

3. **Extend DAOs with relationship queries** adding methods:
   - `forum_items DAO`: `getItemsByProject($project_id)`, `getItemsByStatus($status)`, `getItemsByAssignee($user_id)`, `getItemsByLabel($label_id)`, `getItemsByMilestone($milestone_id)`
   - Helper methods: `getAssignees($item_id)`, `getLabels($item_id)`, `getRelatedItems($item_id)`, `getProjects($item_id)`
   - Use `dtoSet` for collections and leverage APC caching via `getByID()`
   - Override `Insert()` to auto-set `created_by` from `currentUser::id()`

4. **Build handler methods** in each module's `handler.php` for AJAX operations:
   - Forum items: `itemSave`, `itemDelete`, `assignUser`, `unassignUser`, `addLabel`, `removeLabel`, `updateStatus`, `linkItem`, `unlinkItem`
   - Projects: `projectSave`, `projectDelete`, `addItem`, `removeItem`
   - Labels: `labelSave`, `labelDelete`
   - Milestones: `milestoneSave`, `milestoneDelete`
   - Return `json::ack()` for success or `json::nak()` for errors

5. **Implement three view modes** in forum_items controller:
   - **Table view** (`views/matrix.php`): Bootstrap table with columns for title, status, assignees, labels, project, milestone, due date; add sorting and filtering
   - **Board view** (`views/board.php`): Kanban columns grouped by status (Backlog, Todo, In Progress, Done); implement drag-drop with JavaScript to update status
   - **Timeline view** (`views/timeline.php`): Gantt chart using dayjs library for date rendering; show items on timeline by due_date with milestone markers
   - Add view switcher buttons in navbar to toggle between views

6. **Create edit modals** extending `bravedave\dvc\esse\modal` in `views/edit.php`:
   - Form fields: title, description, status dropdown, priority dropdown
   - Multi-select for assignees (populated from users table via `currentUser` integration)
   - Multi-select for labels (with color badges)
   - Project selector dropdown
   - Milestone selector dropdown
   - Related items autocomplete/search
   - Due date picker
   - Use existing Bootstrap 5 styles and `_brayworth_` JavaScript library for AJAX submissions
   - Emit 'success' event on save to refresh parent view

## Source Tree Cache

Use the file `.github/copilot_cache/source_tree.txt` as the authoritative
list of all source files in the project. Do not run shell commands such as
“find”, “ls”, or directory scans. Treat the cached file as the complete and
current structure unless explicitly told otherwise.

Whenever you create, rename, or delete files as part of a plan, update
`.github/copilot_cache/source_tree.txt` accordingly:

- Insert new files into the list in sorted order.
- Remove entries for deleted files.
- Keep the file alphabetically sorted at all times.

If the cache is missing or empty, request that it be regenerated instead of
querying the filesystem.

Always read this file first before attempting to reason about project layout.

## Further Considerations

1. **View state persistence**: Store user's preferred view (table/board/timeline) in `currentUser::option('view_mode')` and restore on page load?

2. **Real-time collaboration**: Use existing `bravedave\dvc\push.php` WebSocket for live updates when items change, or use polling via AJAX every 30 seconds?

3. **Item relationships**: Support typed relationships (blocks/blocked-by, relates-to, duplicates, parent/child) with `relation_type` field, or keep simple untyped links only?

4. **Bulk operations**: Enable multi-select checkboxes for batch operations like status updates, label assignments, project moves, bulk delete?

5. **Activity tracking**: Add `forum_item_history` table to track all changes (status changes, assignments, label changes) with timestamp and user_id for audit trail?

6. **Search and filtering**: Implement advanced search with filters for status, assignee, label, project, milestone, date ranges? Use full-text search on title/description?

7. **Permissions**: Assume permissions are granted globally - no access control implementation needed for this project management system.

8. **Comments/discussions**: Add `forum_item_comments` table for threaded discussions on items?

9. **Notifications**: ~~Email/push notifications when assigned, mentioned, or item status changes?~~ (Not implementing for this project)

10. **Custom fields**: Support user-defined custom fields per project (text, number, date, dropdown)?
