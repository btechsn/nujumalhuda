"use client";

import { useEffect, useRef } from "react";

type LeafletRuntime = {
  map: (el: HTMLElement, options: { scrollWheelZoom: boolean }) => LeafletMap;
  tileLayer: (url: string, options: { maxZoom: number; attribution: string }) => { addTo: (map: LeafletMap) => void };
  marker: (point: [number, number]) => { addTo: (map: LeafletMap) => { bindPopup: (text: string) => void } };
  Icon: { Default: { prototype: { _getIconUrl?: () => string }; mergeOptions: (options: Record<string, string>) => void } };
};

type LeafletMap = { remove: () => void; invalidateSize: () => void };

declare global {
  interface Window {
    L?: LeafletRuntime;
  }
}

let leafletReady: Promise<void> | null = null;

function ensureLeaflet(): Promise<void> {
  if (typeof window === "undefined") return Promise.resolve();
  if (window.L) return Promise.resolve();
  if (leafletReady) return leafletReady;

  leafletReady = new Promise((resolve, reject) => {
    if (!document.querySelector("link[data-nh-leaflet]")) {
      const css = document.createElement("link");
      css.rel = "stylesheet";
      css.href = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css";
      css.dataset.nhLeaflet = "1";
      document.head.appendChild(css);
    }
    const script = document.createElement("script");
    script.src = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
    script.onload = () => resolve();
    script.onerror = () => reject(new Error("leaflet"));
    document.head.appendChild(script);
  });

  return leafletReady;
}

export function PlaceMap({
  latitude,
  longitude,
  label,
  className = "h-[28rem] w-full",
}: {
  latitude: number;
  longitude: number;
  label: string;
  className?: string;
}) {
  const canvas = useRef<HTMLDivElement>(null);

  useEffect(() => {
    let map: LeafletMap | null = null;
    let cancelled = false;

    ensureLeaflet()
      .then(() => {
        const L = window.L;
        const node = canvas.current;
        if (cancelled || !L || !node || node.dataset.ready === "1") return;
        node.dataset.ready = "1";
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
          iconRetinaUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png",
          iconUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png",
          shadowUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",
        });
        map = L.map(node, { scrollWheelZoom: false }).setView([latitude, longitude], 16);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          maxZoom: 19,
          attribution: "&copy; OpenStreetMap",
        }).addTo(map);
        L.marker([latitude, longitude]).addTo(map).bindPopup(label);
        window.setTimeout(() => map?.invalidateSize(), 200);
      })
      .catch(() => undefined);

    return () => {
      cancelled = true;
      map?.remove();
      if (canvas.current) delete canvas.current.dataset.ready;
    };
  }, [latitude, longitude, label]);

  return <div ref={canvas} className={className} role="img" aria-label={label} />;
}
