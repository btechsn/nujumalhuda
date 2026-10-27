"use client";

import { useEffect, useState } from "react";
import { useTranslations } from "next-intl";

import { api } from "@/lib/api";
import { readEditorial } from "@/lib/editorial";
import { materialSource, readCachedLesson, storeLesson, type CachedLesson, type CachedMaterial } from "@/lib/lesson-cache";

const CATEGORIES = ["announcements", "prayers", "hadith", "live", "contributions"] as const;

type Category = (typeof CATEGORIES)[number];

type AccountNotification = {
  id: string;
  type: string;
  title: string;
  message: string;
  is_read: boolean;
  created_at: string;
};

function urlBase64ToUint8Array(value: string): Uint8Array {
  const padding = "=".repeat((4 - (value.length % 4)) % 4);
  const base64 = (value + padding).replace(/-/g, "+").replace(/_/g, "/");
  const raw = atob(base64);
  return Uint8Array.from(raw, (char) => char.charCodeAt(0));
}

export function AlertSection() {
  const t = useTranslations("comfort");
  const [prefs, setPrefs] = useState<Record<Category, boolean>>({
    announcements: false,
    prayers: false,
    hadith: false,
    live: false,
    contributions: false,
  });
  const [ready, setReady] = useState(false);
  const [publicKey, setPublicKey] = useState<string | null>(null);
  const [message, setMessage] = useState("");
  const [notifications, setNotifications] = useState<AccountNotification[]>([]);

  useEffect(() => {
    let ignore = false;
    Promise.all([
      api.getNotificationPreferences(),
      fetch(`${process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000/api/v1"}/settings/public`).then((response) =>
        response.json(),
      ),
    ])
      .then(([preferences, settings]) => {
        if (ignore) return;
        const next = { announcements: false, prayers: false, hadith: false, live: false, contributions: false };
        const rows = preferences.data?.preferences ?? [];
        for (const row of rows) {
          if (row.category in next) next[row.category as Category] = Boolean(row.enabled);
        }
        setPrefs(next);
        setPublicKey(settings?.push?.configured ? settings.push.public_key : null);
        setReady(true);
      })
      .catch(() => {
        if (!ignore) setReady(true);
      });
    return () => {
      ignore = true;
    };
  }, []);

  useEffect(() => {
    let ignore = false;
    api.getNotifications().then((response) => {
      if (ignore) return;
      const payload = response.data as any;
      const rows = Array.isArray(payload) ? payload : Array.isArray(payload?.data) ? payload.data : [];
      setNotifications(rows.filter((item: AccountNotification) =>
        item.type === "dahira.contribution_due" ||
        item.type.startsWith("announcement.") ||
        item.type.startsWith("prayer.") ||
        item.type === "hadith.daily",
      ));
    }).catch(() => {
      if (!ignore) setNotifications([]);
    });
    return () => { ignore = true; };
  }, []);

  async function markRead(id: string) {
    try {
      await api.markNotificationRead(id);
      setNotifications((current) => current.map((item) => item.id === id ? { ...item, is_read: true } : item));
    } catch {
      setMessage(t("notificationReadError"));
    }
  }

  async function toggle(category: Category) {
    setMessage("");
    const enabled = !prefs[category];
    try {
      if (enabled) {
        if (!("Notification" in window) || !("PushManager" in window)) {
          setMessage(t("alertsUnsupported"));
          return;
        }
        if (!publicKey) {
          setMessage(t("alertsNeedPush"));
          return;
        }
        const permission = await Notification.requestPermission();
        if (permission !== "granted") {
          setMessage(t("alertsDenied"));
          return;
        }
        const registration = await navigator.serviceWorker.ready;
        let subscription = await registration.pushManager.getSubscription();
        if (!subscription) {
          const applicationServerKey = urlBase64ToUint8Array(publicKey);
          subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey as BufferSource,
          });
        }
        const json = subscription.toJSON();
        await api.subscribePush({
          endpoint: subscription.endpoint,
          public_key: json.keys?.p256dh || "",
          auth_token: json.keys?.auth || "",
          content_encoding: "aes128gcm",
        });
      }

      await api.updateNotificationPreference(category, enabled);
      const next = { ...prefs, [category]: enabled };
      setPrefs(next);

      if (!enabled && !Object.values(next).some(Boolean)) {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();
        if (subscription) {
          await api.unsubscribePush(subscription.endpoint);
          await subscription.unsubscribe();
        }
      }
    } catch {
      setMessage(t("alertsNeedPush"));
    }
  }

  return (
    <div>
      <h2 className="font-sans text-xl font-extrabold text-content">{t("notificationsInbox")}</h2>
      {notifications.length ? (
        <ul className="mt-4 space-y-3">
          {notifications.map((notification) => (
            <li key={notification.id} className={`rounded-2xl p-4 ring-1 ring-black/5 ${notification.is_read ? "bg-[#f8f9fa]" : "bg-gold-100/50"}`}>
              <div className="flex items-start justify-between gap-3">
                <div>
                  <p className="text-sm font-bold text-content">{notification.title}</p>
                  <p className="mt-1 text-sm text-content-secondary">{notification.message}</p>
                  <p className="mt-2 text-xs text-content-secondary">{new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(new Date(notification.created_at))}</p>
                </div>
                {!notification.is_read ? (
                  <button type="button" onClick={() => void markRead(notification.id)} className="shrink-0 text-xs font-semibold text-brand-800 underline">{t("markRead")}</button>
                ) : null}
              </div>
            </li>
          ))}
        </ul>
      ) : (
        <p className="mt-3 rounded-xl bg-[#f8f9fa] p-4 text-sm text-content-secondary">{t("noContributionNotifications")}</p>
      )}
      <h2 className="mt-8 font-sans text-xl font-extrabold text-content">{t("push")}</h2>
      <p className="mt-2 text-sm text-content-secondary">{t("alertsLede")}</p>
      <ul className="mt-6 space-y-3">
        {CATEGORIES.map((category) => (
          <li key={category} className="flex items-center justify-between gap-4 rounded-2xl bg-[#f8f9fa] px-4 py-3">
            <span className="text-sm font-semibold text-content">{t(category)}</span>
            <button
              type="button"
              role="switch"
              aria-checked={prefs[category]}
              disabled={!ready}
              onClick={() => void toggle(category)}
              className={`relative h-7 w-12 rounded-full transition ${prefs[category] ? "bg-brand-800" : "bg-black/15"}`}
            >
              <span
                className={`absolute top-0.5 h-6 w-6 rounded-full bg-white transition ${prefs[category] ? "start-5" : "start-0.5"}`}
              />
              <span className="sr-only">{prefs[category] ? t("alertsOn") : t("alertsOff")}</span>
            </button>
          </li>
        ))}
      </ul>
      {message ? <p className="mt-4 text-sm text-content-secondary">{message}</p> : null}
    </div>
  );
}

export function LessonSection({ locale }: { locale: string }) {
  const t = useTranslations("comfort");
  const [lesson, setLesson] = useState<CachedLesson | null>(null);
  const [sources, setSources] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(true);
  const [offline, setOffline] = useState(false);

  useEffect(() => {
    let ignore = false;
    api
      .getCurrentLesson()
      .then(async (response) => {
        if (ignore) return;
        const data = response.data as CachedLesson | null;
        if (!data) {
          setLesson(await readCachedLesson());
          setOffline(true);
        } else {
          setLesson(await storeLesson(data));
        }
        setLoading(false);
      })
      .catch(async () => {
        if (ignore) return;
        setLesson(await readCachedLesson());
        setOffline(true);
        setLoading(false);
      });
    return () => {
      ignore = true;
    };
  }, []);

  useEffect(() => {
    if (!lesson) return;
    let cancelled = false;
    Promise.all(
      lesson.materials.map(async (material) => [material.url, await materialSource(material)] as const),
    ).then((pairs) => {
      if (!cancelled) setSources(Object.fromEntries(pairs));
    });
    return () => {
      cancelled = true;
    };
  }, [lesson]);

  if (loading) {
    return <div className="h-40 animate-pulse rounded-2xl bg-[#f3f4f6]" />;
  }

  if (!lesson) {
    return <p className="text-sm text-content-secondary">{t("lessonEmpty")}</p>;
  }

  const title = readEditorial(lesson.title, locale).text;
  const content = lesson.content ? readEditorial(lesson.content, locale).text : "";
  const course = lesson.course ? readEditorial(lesson.course, locale).text : "";

  return (
    <div>
      <h2 className="font-sans text-xl font-extrabold text-content">{title}</h2>
      {course ? <p className="mt-1 text-sm text-content-secondary">{course}</p> : null}
      <p className="mt-3 text-sm text-brand-800">{offline ? t("lessonOffline") : t("lessonCached")}</p>
      {content ? <p className="mt-4 whitespace-pre-wrap text-sm leading-6 text-content">{content}</p> : null}
      <ul className="mt-6 space-y-4">
        {lesson.materials.map((material) => (
          <li key={material.url}>
            <MaterialRow material={material} source={sources[material.url] || material.url} label={t(material.kind === "audio" ? "lessonAudio" : "lessonFile")} />
          </li>
        ))}
      </ul>
    </div>
  );
}

function MaterialRow({ material, source, label }: { material: CachedMaterial; source: string; label: string }) {
  if (material.kind === "audio") {
    return (
      <div>
        <p className="mb-2 text-sm font-semibold text-content">{material.name}</p>
        <audio controls src={source} className="w-full" />
      </div>
    );
  }

  return (
    <a href={source} target="_blank" rel="noopener" className="text-sm font-semibold text-brand-800">
      {label} — {material.name}
    </a>
  );
}
