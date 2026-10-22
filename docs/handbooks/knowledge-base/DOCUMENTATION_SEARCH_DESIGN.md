# Documentation Search Design

1. Normalize the question to lowercase, trim whitespace, and tokenize words.
2. Match exact question variants first.
3. Match title terms, then keywords, then aliases.
4. Rank by exact-question match, matched keywords, category, and audience.
5. Return the top entry with answer summary, full document link, related entries, and troubleshooting links.

The future path is `Admin -> Help -> Ask a Question -> deterministic search -> process and related troubleshooting`. No model, chatbot, external API, or LLM is required.
