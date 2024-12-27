// src/global.d.ts
export {};

declare global {
    interface Window {
        __GLOBAL_CONST__: {
            AUTH_URL: string;
        };
    }
}
