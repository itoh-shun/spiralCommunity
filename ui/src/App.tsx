import './App.css';
import {LocalizationProvider} from "@mui/x-date-pickers";
import {AdapterDateFns} from '@mui/x-date-pickers/AdapterDateFnsV3'
import {DialogsProvider, Session} from "@toolpad/core";
import {AppProvider} from "@toolpad/core/AppProvider";
import {ja} from "date-fns/locale";
import {useEffect, useMemo, useState} from "react";
import {BrowserRouter, Route, Routes} from "react-router-dom";
import Layout from "./components/Layout.tsx";
import Home from "./pages/Home.tsx";
import UserInfo from "./pages/UserInfo.tsx";
import Users from "./pages/Users.tsx";
import {base64ToTemporaryURL} from "./utils/base64ToTemporaryURL.ts";

import("easymde/dist/easymde.min.css")

export type UserSession = Session & {
    user: {
        permission?: 'Admin' | 'User',
    }
}

const App = () => {
    const [session, setSession] = useState<UserSession | null>(null);

    // APIからセッションデータを取得
    const fetchSession = async () => {
        try {
            // @ts-ignore
            const {API_URL} = window.__GLOBAL_CONST__;
            const url = new URL(API_URL);
            url.searchParams.set('_path', 'api/login/me');
            const response = await fetch(url); // APIのURL
            if (response.ok) {
                const data = await response.json();

                // ユーザー画像をBlob URLに変換
                const userImageURL = data.userImage
                    ? base64ToTemporaryURL(data.userImage, "image/png")
                    : null;

                setSession({
                    user: {
                        id: data.id,
                        name: data.display_name,
                        email: data.email,
                        image: userImageURL, // 一時URLをセット
                        permission: data.permission
                    },
                });
            } else {
                console.error("Failed to fetch session data");
            }
        } catch (error) {
            console.error("Error fetching session:", error);
        }
    };

    const authentication = useMemo(() => {
        return {
            signIn: fetchSession, // APIを使ってログイン処理を再利用
            signOut: () => {
                if (session?.user?.image) {
                    URL.revokeObjectURL(session.user.image); // 一時URLを解放
                }
                setSession(null); // セッションをリセット
            },
        };
    }, [session]);

    // 初回レンダリング時にAPIを呼び出す
    useEffect(() => {
        fetchSession();
        // クリーンアップ処理
        return () => {
            if (session?.user?.image) {
                URL.revokeObjectURL(session.user.image); // メモリ解放
            }
        };
    }, []);

    return (
        <AppProvider authentication={authentication} session={session}>
            <LocalizationProvider dateAdapter={AdapterDateFns} adapterLocale={ja}>
                <DialogsProvider>
                    <BrowserRouter>
                        <Routes>
                            <Route path="/" element={<Layout/>}>
                                <Route index element={<Home/>}/>
                                <Route path="/users" element={<Users/>}/>
                                <Route path="/users/:userId" element={<UserInfo/>}/>
                            </Route>
                        </Routes>
                    </BrowserRouter>
                </DialogsProvider>
            </LocalizationProvider>
        </AppProvider>
    );
};

export default App;
