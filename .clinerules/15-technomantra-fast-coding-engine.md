# Technomantra Fast Coding Engine V4.11.17

This rule optimizes coding speed without reducing quality, accuracy, model strength, or source safety. It applies to normal HTML/CSS/JS, React/Vite/Next, Laravel/PHP, and Node.js tasks.

## Core execution lane
1. Use the local Project Memory and Local Code Knowledge Graph as the first locator. They are indexes, not source truth; read current source only for the small target bundle before editing.
2. Normal targeted work must follow RESOLVE -> READ SMALL -> EDIT -> VERIFY. Do not add a planning/discovery loop between steps once the target is known.
3. Prefer one batched discovery pass: search/selectors/routes/symbols first, then read only the direct source ranges needed. Avoid one AI turn per file.
4. A normal small edit should reach the first source edit within 2-4 meaningful AI/tool turns. A normal React/Laravel/Node bug should reach the first source edit within 4-7 turns once the route/component/handler chain is identified.
5. If no source edit has happened after the fast-lane budget, stop broad discovery, perform one final targeted lookup, then edit or ask one precise blocker question.

## Fast source reading
1. Search before reading large files. Use visible text, DOM selector, CSS class/id, route path, controller/action, component name, function name, API path, or error text.
2. Read small ranges around matches, normally 80-200 lines. Read an entire file only when it is genuinely small or the whole file is required to preserve behavior.
3. Reuse unchanged evidence and hash/index facts. Do not reread unchanged full files simply because the conversation is long.
4. For repeated source needs, prefer current small-range refreshes over restoring old tool output.

## Fast writing
1. Edit the smallest stable block: one component, one selector group, one route/controller/service chain, or one template section.
2. Do not rewrite an entire multi-thousand-line file for a small text, image, color, button, spacing, or validation change.
3. Use deterministic anchored edits. If an exact edit misses once, refresh the current small range and rebuild the patch from current source.
4. Preserve imports, props, hooks, routes, middleware, validation, database semantics, auth, and existing UI conventions unless the prompt explicitly changes them.

## Fast verification
1. Match validation to risk. Small static/UI edit: targeted search/range/syntax check. React component edit: relevant build/typecheck/lint when available. Laravel/Node backend edit: syntax or one targeted test/API check.
2. Run one meaningful validation after an edit. If it fails, fix the first actionable error before running the same command again.
3. Do not start or restart long-running dev servers repeatedly. Use an already-running preview when available; start one only when visual/runtime verification truly requires it.
4. Once the requested behavior is implemented and one relevant verification succeeds or a concrete environment blocker is proven, finish. Do not continue unrelated cleanup.

## Framework shortcuts
1. React/Vite/Next: route/page -> component -> direct child/component -> hook/state/service/API -> style file. Do not inspect backend unless data/API behavior changes.
2. Laravel: route -> controller/Filament action -> FormRequest -> service/action/repository -> model/resource/view. Avoid vendor, storage/framework, and unrelated migrations.
3. Node.js: route -> middleware -> controller/handler -> service -> repository/model/database. Avoid frontend exploration for backend-only tasks.
4. Static websites: page/section text or selector -> nearby markup -> related CSS/JS -> bounded edit -> targeted verification.

Quality rule: speed comes from better targeting, batching, caching, and bounded verification. It must never come from guessing, skipping current source, weakening validation for risky edits, or silently changing the selected model.
