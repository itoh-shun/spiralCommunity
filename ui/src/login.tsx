// src/login.tsx
import React from 'react';
import LoginPage from "./pages/LoginPage";
import {Container} from "@mui/material";
import {createRoot} from "react-dom/client";

createRoot(document.getElementById('login-root')!).render(
    <React.StrictMode>
        <Container>
            <LoginPage />
        </Container>
    </React.StrictMode>,
);