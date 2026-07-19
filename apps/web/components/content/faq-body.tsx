export type FaqEntry = { question: string; answer: string };
export type FaqBlock =
  | ({ type: "question" } & FaqEntry)
  | { type: "text"; text: string };

export function getFaqBlocks(content: string): FaqBlock[] {
  return content
    .split(/\n\s*\n/)
    .map((block) => block.trim())
    .filter(Boolean)
    .map((block) => {
      const [question, ...answer] = block.split("\n");
      return answer.length > 0 && /[?？]$/.test(question.trim())
        ? { type: "question" as const, question, answer: answer.join(" ") }
        : { type: "text" as const, text: block.replace(/\n+/g, " ") };
    });
}

export function getFaqEntries(blocks: FaqBlock[]): FaqEntry[] {
  return blocks.filter((block): block is Extract<FaqBlock, { type: "question" }> => block.type === "question");
}

export function FaqBody({ blocks }: { blocks: FaqBlock[] }) {
  return (
    <div className="space-y-4">
      {blocks.map((block, index) => block.type === "question" ? (
          <details key={`${block.question}-${index}`} className="group rounded-2xl border border-stone-200 bg-white p-5 open:border-amber-300">
            <summary className="cursor-pointer list-none pr-8 text-lg font-bold text-stone-950 marker:hidden">
              {block.question}
              <span aria-hidden="true" className="float-right text-amber-800 group-open:rotate-45">+</span>
            </summary>
            <p className="mt-4 border-t border-stone-100 pt-4 leading-7 text-stone-600">{block.answer}</p>
          </details>
        ) : <p key={`${block.text.slice(0, 24)}-${index}`} className="text-lg leading-8 text-stone-700">{block.text}</p>)}
    </div>
  );
}
