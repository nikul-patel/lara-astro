import type { Astrologer } from "@/lib/api";

type AstrologerPhotoProps = {
  astrologer: Astrologer;
  alt: string;
  className?: string;
};

export function AstrologerPhoto({
  astrologer,
  alt,
  className = "size-full object-cover",
}: AstrologerPhotoProps) {
  if (astrologer.photo_url) {
    return (
      <>
        {/* Tenant media may be served by any configured Laravel API host. */}
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          src={astrologer.photo_url}
          alt={alt}
          width={480}
          height={600}
          className={className}
        />
      </>
    );
  }

  return (
    <span aria-hidden="true" className="text-6xl font-bold text-amber-800">
      {astrologer.name.charAt(0)}
    </span>
  );
}
