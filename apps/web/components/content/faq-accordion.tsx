type FaqItem = { question: string; answer: string };

// The CMS stores FAQ content as blocks separated by a blank line, where the
// first line of each block is the question (ending with "?") and the remaining
// lines are the answer. Any block that does not look like a Q/A pair is still
// surfaced as a plain paragraph so no content is silently dropped.
export function parseFaqContent(content: string): { items: FaqItem[]; extra: string[] } {
  const items: FaqItem[] = [];
  const extra: string[] = [];
  content
    .split(/\n\s*\n/)
    .map((block) => block.trim())
    .filter(Boolean)
    .forEach((block) => {
      const lines = block.split("\n").map((line) => line.trim()).filter(Boolean);
      if (lines.length > 1 && lines[0].endsWith("?")) {
        items.push({ question: lines[0], answer: lines.slice(1).join(" ") });
      } else {
        extra.push(block);
      }
    });
  return { items, extra };
}

export function FaqAccordion({ content }: { content: string }) {
  const { items, extra } = parseFaqContent(content);

  return (
    <div className="space-y-4">
      {extra.length > 0 && (
        <div className="space-y-4 text-lg leading-8 text-stone-700">
          {extra.map((block, index) => (
            <p key={`faq-intro-${index}`}>{block}</p>
          ))}
        </div>
      )}
      <ul className="space-y-3">
        {items.map((item, index) => (
          <li key={`${item.question}-${index}`}>
            <details className="group rounded-2xl border border-stone-200 bg-white [&_summary::-webkit-details-marker]:hidden">
              <summary className="flex cursor-pointer items-center justify-between gap-4 rounded-2xl px-5 py-4 text-lg font-bold text-stone-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-700">
                <span>{item.question}</span>
                <span aria-hidden="true" className="text-2xl text-amber-700 transition-transform duration-200 group-open:rotate-45">+</span>
              </summary>
              <div className="px-5 pb-5 text-base leading-7 text-stone-700">{item.answer}</div>
            </details>
          </li>
        ))}
      </ul>
    </div>
  );
}
