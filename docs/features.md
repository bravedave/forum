Using CHAT Cli

# to create a module

```bash
/run task "Make DVC Module" with moduleName=ideas fields=title,description
```


# Use plans or prompt files to anchor context

Since you already have .github/prompts/plan-*.prompt.md, you can do:

```bash
/use plan-projectManagementSystem
```

then:

```bash
/edit file: src/app/ideas/controller.php
Extend this controller to support timeline view as described in the plan.
```

Copilot loads your architectural context and edits accordingly.