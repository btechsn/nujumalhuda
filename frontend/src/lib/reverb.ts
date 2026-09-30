/**
 * Configuration Laravel Reverb (WebSocket) pour Nujum Al-Huda Center
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
  interface Window {
    Pusher: typeof Pusher;
    Echo: Echo | undefined;
  }
}

// Exposer Pusher globalement (requis par Laravel Echo)
if (typeof window !== 'undefined') {
  window.Pusher = Pusher;
}

const WS_URL = process.env.NEXT_PUBLIC_WS_URL || 'ws://localhost/ws';
const REVERB_APP_KEY = process.env.NEXT_PUBLIC_REVERB_APP_KEY || 'nujumalhuda';

let echoInstance: Echo | null = null;

export function initializeEcho(): Echo {
  if (echoInstance) {
    return echoInstance;
  }

  if (typeof window === 'undefined') {
    throw new Error('Echo can only be initialized on the client side');
  }

  echoInstance = new Echo({
    broadcaster: 'reverb',
    key: REVERB_APP_KEY,
    wsHost: new URL(WS_URL).hostname,
    wsPort: new URL(WS_URL).port || (WS_URL.startsWith('wss') ? 443 : 80),
    wssPort: new URL(WS_URL).port || 443,
    forceTLS: WS_URL.startsWith('wss'),
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${process.env.NEXT_PUBLIC_API_URL}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('auth_token') || ''}`,
      },
    },
  });

  return echoInstance;
}

export function getEcho(): Echo {
  if (!echoInstance) {
    return initializeEcho();
  }
  return echoInstance;
}

export function disconnectEcho(): void {
  if (echoInstance) {
    echoInstance.disconnect();
    echoInstance = null;
  }
}
