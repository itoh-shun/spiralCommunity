// src/pages/LoginPage.tsx
import { AuthProvider, AuthResponse, SignInPage } from "@toolpad/core";
import {ReactNode} from "react";
import { AppProvider } from '@toolpad/core/AppProvider';
import { useTheme } from '@mui/material/styles';

function LoginPage(): ReactNode {
    const { AUTH_URL } = window.__GLOBAL_CONST__;
    const providers = [
        { id: 'azure-ad', name: 'Microsoft' },
    ];

    const signIn = async (provider: AuthProvider): Promise<AuthResponse> => {
        return new Promise<AuthResponse>((resolve) => {
            setTimeout(() => {
                if (provider.id === 'azure-ad') {
                    location.href = AUTH_URL;
                    resolve({ success: 'ページ遷移します' });
                } else {
                    resolve({ error: 'This is a fake error' });
                }
            }, 500);
        });
    };

    const theme = useTheme();

    return (
        <AppProvider theme={theme}>
            <SignInPage signIn={signIn} providers={providers} sx={{
                mx: "auto"
            }} />
        </AppProvider>
    )
}

export default LoginPage;