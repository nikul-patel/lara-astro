export type FaqEntry = { question: string; answer: string };

export function getFaqEntries(content: string): FaqEntry[] {
  return content
    .split(/\n\s*\n/)
    .map((block) => block.trim().split("\n"))
    .filter((lines) => lines.length > 1)
    .map(([question, ...answer]) => ({ question, answer: answer.join(" ") }));
}

export function FaqBody({ entries }: { entries: FaqEntry[] }) {
  return (
    <div className="space-y-4">
      {entries.map((entry, index) => (
        <details key={`${entry.question}-${index}`} className="group rounded-2xl border border-stone-200 bg-white p-5 open:border-amber-300">
          <summary className="cursor-pointer list-none pr-8 text-lg font-bold text-stone-950 marker:hidden">
            {entry.question}
            <span aria-hidden="true" className="float-right text-amber-800 group-open:rotate-45">+</span>
          </summary>
          <p className="mt-4 border-t border-stone-100 pt-4 leading-7 text-stone-600">{entry.answer}</p>
        </details>
      ))}
    </div>
  );
}
