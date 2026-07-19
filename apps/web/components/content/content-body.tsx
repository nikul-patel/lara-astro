export function ContentBody({ content }: { content: string }) {
  const blocks = content.split(/\n\s*\n/).map((block) => block.trim()).filter(Boolean);

  return (
    <div className="space-y-6 text-lg leading-8 text-stone-700">
      {blocks.map((block, index) => {
        const lines = block.split("\n");
        if (lines.length > 1 && lines[0].endsWith("?")) {
          return <section key={`${lines[0]}-${index}`}><h2 className="text-xl font-bold text-stone-950">{lines[0]}</h2><p className="mt-2">{lines.slice(1).join(" ")}</p></section>;
        }
        return <p key={`${block.slice(0, 24)}-${index}`}>{block}</p>;
      })}
    </div>
  );
}
