"use client";

import { FormEvent, useEffect, useId, useRef, useState } from "react";
import { useTranslations } from "next-intl";

type ChatMessage = {
  id: string;
  role: "user" | "assistant";
  text: string;
};

/**
 * Bouton flottant Support IA — front uniquement pour l'instant.
 * Le branchement API se fera plus tard via une clé.
 */
export function AiSupportFab() {
  const t = useTranslations("aiSupport");
  const panelId = useId();
  const [open, setOpen] = useState(false);
  const [input, setInput] = useState("");
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const listRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!open) return;
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") setOpen(false);
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [open]);

  useEffect(() => {
    listRef.current?.scrollTo({ top: listRef.current.scrollHeight, behavior: "smooth" });
  }, [messages, open]);

  const send = (event: FormEvent) => {
    event.preventDefault();
    const text = input.trim();
    if (!text) return;

    const userMessage: ChatMessage = {
      id: `u-${Date.now()}`,
      role: "user",
      text,
    };
    const assistantMessage: ChatMessage = {
      id: `a-${Date.now()}`,
      role: "assistant",
      text: t("placeholderReply"),
    };

    setMessages((current) => [...current, userMessage, assistantMessage]);
    setInput("");
  };

  return (
    <div className="pointer-events-none fixed bottom-5 end-5 z-[60] flex flex-col items-end gap-3 sm:bottom-6 sm:end-6">
      {open ? (
        <div
          id={panelId}
          role="dialog"
          aria-label={t("title")}
          className="pointer-events-auto flex h-[min(28rem,70dvh)] w-[min(22rem,calc(100vw-2.5rem))] flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-2xl"
        >
          <header className="flex items-center justify-between gap-3 bg-[#E67E22] px-4 py-3 text-white">
            <div>
              <p className="font-sans text-sm font-extrabold">{t("title")}</p>
              <p className="text-xs text-white/85">{t("subtitle")}</p>
            </div>
            <button
              type="button"
              onClick={() => setOpen(false)}
              className="rounded-full p-1.5 hover:bg-white/15"
              aria-label={t("close")}
            >
              <CloseIcon />
            </button>
          </header>

          <div ref={listRef} className="flex-1 space-y-3 overflow-y-auto bg-[#fafafa] px-3 py-3">
            {messages.length === 0 ? (
              <p className="rounded-xl bg-white px-3 py-2.5 text-sm leading-relaxed text-content-secondary shadow-sm">
                {t("welcome")}
              </p>
            ) : (
              messages.map((message) => (
                <div
                  key={message.id}
                  className={
                    message.role === "user"
                      ? "ms-8 rounded-xl bg-[#E67E22] px-3 py-2 text-sm text-white"
                      : "me-8 rounded-xl bg-white px-3 py-2 text-sm text-content shadow-sm"
                  }
                >
                  {message.text}
                </div>
              ))
            )}
          </div>

          <form onSubmit={send} className="flex gap-2 border-t border-line bg-white p-3">
            <input
              value={input}
              onChange={(event) => setInput(event.target.value)}
              placeholder={t("inputPlaceholder")}
              className="min-w-0 flex-1 rounded-full border border-line bg-white px-3 py-2 text-sm outline-none ring-[#E67E22]/25 focus:ring-2"
            />
            <button
              type="submit"
              className="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-[#E67E22] text-white hover:brightness-105"
              aria-label={t("send")}
            >
              <SendIcon />
            </button>
          </form>
        </div>
      ) : null}

      <button
        type="button"
        onClick={() => setOpen((value) => !value)}
        aria-expanded={open}
        aria-controls={open ? panelId : undefined}
        aria-label={t("title")}
        className="pointer-events-auto inline-flex size-14 items-center justify-center rounded-full bg-[#E67E22] text-white shadow-[0_4px_14px_rgba(0,0,0,0.28)] transition hover:scale-105 hover:brightness-105 active:scale-95"
      >
        {open ? <CloseIcon /> : <ChatBubblesIcon />}
      </button>
    </div>
  );
}

function ChatBubblesIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" className="size-7">
      <path d="M8.2 4.5h9.3A3.5 3.5 0 0 1 21 8v5.2a3.5 3.5 0 0 1-3.5 3.5H14l-3.2 2.4a.7.7 0 0 1-1.1-.6v-1.8H8.2A3.5 3.5 0 0 1 4.7 13.2V8A3.5 3.5 0 0 1 8.2 4.5Z" opacity="0.45" />
      <path d="M3.8 7.2h10.2A3.2 3.2 0 0 1 17.2 10.4v5.1a3.2 3.2 0 0 1-3.2 3.2H9.8l-3.1 2.3a.65.65 0 0 1-1.05-.55v-1.75H3.8A3.2 3.2 0 0 1 .6 15.5v-5.1A3.2 3.2 0 0 1 3.8 7.2Z" />
    </svg>
  );
}

function CloseIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" className="size-5">
      <path d="M5 5l10 10M15 5 5 15" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" />
    </svg>
  );
}

function SendIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" className="size-4">
      <path d="M3.2 10.1 16.4 3.4a.7.7 0 0 1 1 .8l-3.2 12.2a.7.7 0 0 1-1.1.4l-3.6-2.7-2.1 2.1a.6.6 0 0 1-1-.4v-3.4L3.1 10.9a.7.7 0 0 1 .1-.8Z" />
    </svg>
  );
}
