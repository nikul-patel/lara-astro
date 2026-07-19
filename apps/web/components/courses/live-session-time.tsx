"use client";

import { useSyncExternalStore } from "react";

const subscribe = () => () => undefined;

export function LiveSessionTime({
  startsAt,
  locale,
}: {
  startsAt: string;
  locale: string;
}) {
  const formatter = (timeZone?: string) =>
    new Intl.DateTimeFormat(locale, {
      dateStyle: "full",
      timeStyle: "short",
      ...(timeZone ? { timeZone } : {}),
    }).format(new Date(startsAt));

  const label = useSyncExternalStore(
    subscribe,
    () => formatter(),
    () => `${formatter("UTC")} UTC`,
  );

  return <time dateTime={startsAt}>{label}</time>;
}
