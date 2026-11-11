.github/copilot-instructions.md:





# Coding Guidelines

Write code that is simple, clean, readable, and easy for another programmer to understand.

## 1. General Principles

- Prioritize readability and maintainability over clever or overly short code.
- Keep the code simple and avoid unnecessary complexity.
- Write code that can be easily modified, extended, and debugged.
- Follow the existing project structure and coding style.
- Do not introduce unnecessary libraries, patterns, abstractions, or dependencies.
- Do not over-engineer simple problems.

## 2. Naming

- Use clear and descriptive names for variables, functions, classes, components, and files.
- Names must explain what the code represents or does.
- Avoid unclear names such as `x`, `y`, `a`, `b`, `tmp`, or `data` unless the meaning is obvious from the context.
- Use consistent naming conventions throughout the project.

## 3. Functions and Methods

- Keep functions small and focused on one responsibility.
- A function should do one clear task.
- Use descriptive function names.
- Avoid functions that contain too many unrelated operations.
- If a function becomes difficult to understand, split it into smaller functions.

## 4. Code Structure

- Organize code according to its responsibility.
- Separate UI, business logic, data handling, configuration, and utilities when appropriate.
- Keep related code together.
- Avoid putting unrelated logic in the same file.
- Follow the existing folder structure before creating new folders or files.

## 5. Readability

- Prefer clear code over clever code.
- Use straightforward logic and control flow.
- Avoid deeply nested conditions when they can be simplified.
- Use formatting and spacing consistently.
- Keep lines reasonably readable.
- Do not compress multiple logical operations into one difficult-to-read line.

## 6. Comments

- Do not add comments for obvious code.
- Add comments only when they help explain non-obvious logic, decisions, limitations, or important behavior.
- Comments should explain why something is done, not simply repeat what the code does.
- Keep comments short and useful.

## 7. Reusable Code

- Reuse existing functions, components, classes, or utilities when appropriate.
- Avoid duplicating the same logic in multiple places.
- Do not create abstractions just to avoid a small amount of duplication.
- Create reusable code only when it improves readability or maintainability.

## 8. Error Handling

- Handle errors clearly and predictably.
- Use meaningful error messages.
- Do not silently ignore important errors.
- Keep error handling easy to understand.

## 9. New Features

Before implementing a new feature:

1. Understand the existing project structure.
2. Check whether similar functionality already exists.
3. Reuse existing code when appropriate.
4. Add the new code in the most logical location.
5. Keep the implementation consistent with the existing project.

Do not unnecessarily rewrite existing code.

## 10. Modifying Existing Code

- Make the smallest reasonable change required to solve the problem.
- Do not modify unrelated files or code.
- Preserve existing functionality unless the change specifically requires it.
- Do not refactor unrelated code without a clear reason.

## 11. Dependencies

- Do not add a new library or package unless it is necessary.
- Prefer existing project dependencies when they are sufficient.
- Explain the reason when a new dependency is required.

## 12. Code Style

- Follow the language's standard conventions.
- Keep formatting consistent across the project.
- Use consistent indentation, naming, quotation style, and syntax.
- Follow the conventions already used in the project when they are reasonable.

## 13. Before Writing Code

Before generating code, understand:

- What the code is supposed to do.
- Where the code belongs.
- How existing code works.
- Whether existing functionality can be reused.

Do not generate code blindly.

## 14. Priority

When making coding decisions, prioritize in this order:

1. Correctness
2. Readability
3. Maintainability
4. Simplicity
5. Performance when necessary

Do not sacrifice readability for minor performance improvements unless there is a real performance requirement.

## 15. Final Rule

The code should be understandable by another programmer who has never seen the project before.

A programmer should be able to open the repository and understand the basic flow without having to decode complicated syntax.

Write code as if another developer will maintain it after you are gone.

## 16. Human-Readable Code

- Prefer explicit and readable syntax over compact or clever syntax.
- Do not shorten code merely to reduce the number of lines.
- Avoid unnecessary shorthand when it makes the code harder to understand.
- Prefer descriptive intermediate variables when they improve readability.
- Write code so that the execution flow can be understood by reading it from top to bottom.
- A few extra lines are acceptable if they make the code significantly easier to understand.

## 17. Simplicity Over Cleverness

- Do not use advanced syntax when a simpler syntax is easier to understand.
- Do not use complex one-liners when multiple simple lines are clearer.
- Avoid unnecessary chaining, nesting, or abstraction.
- Prefer obvious solutions over clever solutions.
- Do not optimize code prematurely.
- Only introduce advanced patterns or techniques when they provide a clear benefit to the project.

## 18. Consistency

- Follow the coding style already established in the project.
- When adding new code, make it look and behave consistently with existing code.
- Do not introduce a different coding style without a clear reason.
- Keep naming, file structure, formatting, and logic patterns consistent throughout the project.

## 19. Maintainability

- Write code that another programmer can modify without needing to understand the entire project first.
- Keep responsibilities separated.
- Avoid hidden behavior and unnecessary side effects.
- Make dependencies between components or functions clear.
- Prefer predictable behavior over clever shortcuts.
- When adding a feature, consider how the code can be extended later.

## 20. Final Coding Philosophy

The goal is not to write the shortest code.

The goal is to write code that is:

- Easy to read.
- Easy to understand.
- Easy to debug.
- Easy to modify.
- Easy to extend.
- Easy for another programmer to maintain.

Prefer simple, explicit, human-readable code over clever, compressed, or unnecessarily advanced code.

Simple code is better than clever code when both produce the same result.