import './App.css';
import {BrowserRouter, Route, Routes} from "react-router-dom";
import Layout from "./components/Layout.tsx";
import Home from "./pages/Home.tsx";
import {AuthenticationContext, Session, SessionContext} from "@toolpad/core";
import React from "react";
import {createTheme} from "@mui/material";
import {AppProvider} from "@toolpad/core/AppProvider";
const App = () => {
    const demoSession = {
        user: {
            name: 'Bharat Kashyap',
            email: 'bharatkashyap@outlook.com',
            image: 'https://avatars.githubusercontent.com/u/19550456',
        },
    };
    const [session, setSession] = React.useState<Session | null>(demoSession);
    const authentication = React.useMemo(() => {
        return {
            signIn: () => {
                setSession(demoSession);
            },
            signOut: () => {
                setSession(null);
            },
        };
    }, []);


    return (
      <AuthenticationContext.Provider value={authentication}>
          <SessionContext.Provider  value={session}>
              <BrowserRouter>
                  <Routes>
                      {/* レイアウトを適用するルート */}
                      <Route path="/" element={<Layout />}>
                          <Route index element={<Home />} />
                      </Route>
                  </Routes>
              </BrowserRouter>
          </SessionContext.Provider>
      </AuthenticationContext.Provider>
  )
}

export default App
