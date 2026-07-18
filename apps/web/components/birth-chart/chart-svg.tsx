import type { ChartResult } from "@/lib/api";
import { getHouses, getPlanetaryPositions } from "@/lib/chart-display";

type ChartSvgProps = {
  result: ChartResult;
  title: string;
};

const perimeterPositions = [
  [50, 50],
  [150, 50],
  [250, 50],
  [350, 50],
  [350, 150],
  [350, 250],
  [350, 350],
  [250, 350],
  [150, 350],
  [50, 350],
  [50, 250],
  [50, 150],
] as const;

const diamondPositions = [
  [200, 55],
  [295, 95],
  [345, 200],
  [295, 305],
  [200, 345],
  [105, 305],
  [55, 200],
  [105, 95],
  [200, 125],
  [275, 200],
  [200, 275],
  [125, 200],
] as const;

const eastIndianPositions = [
  [85, 40],
  [40, 85],
  [200, 66],
  [315, 40],
  [360, 85],
  [334, 200],
  [360, 315],
  [315, 360],
  [200, 334],
  [85, 360],
  [40, 315],
  [66, 200],
] as const;

function chartLines(style: ChartResult["chart_style"]) {
  if (style === "south_indian") {
    return (
      <>
        {[100, 200, 300].map((offset) => (
          <g key={offset}>
            <line x1={offset} y1="0" x2={offset} y2="400" />
            <line x1="0" y1={offset} x2="400" y2={offset} />
          </g>
        ))}
        <rect x="100" y="100" width="200" height="200" fill="#fffbeb" />
      </>
    );
  }

  if (style === "east_indian") {
    return (
      <>
        <line x1="0" y1="133" x2="400" y2="133" />
        <line x1="0" y1="267" x2="400" y2="267" />
        <line x1="133" y1="0" x2="133" y2="400" />
        <line x1="267" y1="0" x2="267" y2="400" />
        <line x1="0" y1="0" x2="133" y2="133" />
        <line x1="400" y1="0" x2="267" y2="133" />
        <line x1="0" y1="400" x2="133" y2="267" />
        <line x1="400" y1="400" x2="267" y2="267" />
      </>
    );
  }

  return (
    <>
      <line x1="0" y1="0" x2="400" y2="400" />
      <line x1="400" y1="0" x2="0" y2="400" />
      <path d="M200 0 L400 200 L200 400 L0 200 Z" />
      <path d="M200 100 L300 200 L200 300 L100 200 Z" />
    </>
  );
}

export function ChartSvg({ result, title }: ChartSvgProps) {
  const houses = getHouses(result.houses);
  const planets = getPlanetaryPositions(result.planetary_positions);
  const isWestern = result.system === "western";
  const resolvedStyle = result.chart_style ?? "north_indian";
  const positions =
    resolvedStyle === "north_indian"
      ? diamondPositions
      : resolvedStyle === "east_indian"
        ? eastIndianPositions
        : perimeterPositions;

  return (
    <svg
      viewBox="0 0 400 400"
      role="img"
      aria-label={title}
      className="aspect-square w-full rounded-3xl bg-amber-50 text-amber-950"
    >
      <title>{title}</title>
      <rect
        x="1"
        y="1"
        width="398"
        height="398"
        rx="20"
        fill="#fffbeb"
        stroke="currentColor"
        strokeWidth="2"
      />

      {isWestern ? (
        <g fill="none" stroke="currentColor" strokeWidth="1.5">
          <circle cx="200" cy="200" r="170" />
          <circle cx="200" cy="200" r="90" />
          {Array.from({ length: 12 }, (_, index) => {
            const angle = (index * Math.PI) / 6 - Math.PI / 2;
            return (
              <line
                key={index}
                x1={200 + Math.cos(angle) * 90}
                y1={200 + Math.sin(angle) * 90}
                x2={200 + Math.cos(angle) * 170}
                y2={200 + Math.sin(angle) * 170}
              />
            );
          })}
        </g>
      ) : (
        <g fill="none" stroke="currentColor" strokeWidth="1.5">
          {chartLines(resolvedStyle)}
        </g>
      )}

      {Array.from({ length: 12 }, (_, index) => {
        const house = houses.find((item) => item.number === index + 1);
        const fallbackPlanet = planets[index]?.name;
        const planetLabel = house?.planets.join(", ") || fallbackPlanet || "";
        const position = isWestern
          ? ([
              200 +
                Math.cos((index * Math.PI) / 6 - Math.PI / 2 + Math.PI / 12) *
                  130,
              200 +
                Math.sin((index * Math.PI) / 6 - Math.PI / 2 + Math.PI / 12) *
                  130,
            ] as const)
          : positions[index];

        return (
          <text
            key={index}
            x={position[0]}
            y={position[1]}
            textAnchor="middle"
            className="fill-current text-[10px] font-semibold"
          >
            <tspan x={position[0]}>{index + 1}</tspan>
            {planetLabel && (
              <tspan x={position[0]} dy="12" className="text-[8px]">
                {planetLabel.slice(0, 12)}
              </tspan>
            )}
          </text>
        );
      })}

      <text
        x="200"
        y="196"
        textAnchor="middle"
        className="fill-amber-800 text-[13px] font-bold"
      >
        {result.system === "western" ? "Tropical" : "Lahiri"}
      </text>
      <text
        x="200"
        y="214"
        textAnchor="middle"
        className="fill-stone-500 text-[9px] uppercase tracking-wider"
      >
        {title}
      </text>
    </svg>
  );
}
