// src/components/Layout.js
import { Outlet } from 'react-router-dom';
import { DashboardLayout} from "@toolpad/core";
import {Chip, createTheme, Stack, Typography} from "@mui/material";
import {CloudCircle} from "@mui/icons-material";
import {AppProvider} from "@toolpad/core/AppProvider";


function CustomAppTitle() {
    return (
        <Stack direction="row" alignItems="center" spacing={2}>
            <CloudCircle fontSize="large" color="primary" />
            <Typography variant="h6">Spiral Community</Typography>
            <Chip size="small" label="BETA" color="info" />
        </Stack>
    );
}

const demoTheme = createTheme({
    cssVariables: {
        colorSchemeSelector: 'data-toolpad-color-scheme',
    },
    colorSchemes: { light: true, dark: true },
    breakpoints: {
        values: {
            xs: 0,
            sm: 600,
            md: 600,
            lg: 1200,
            xl: 1536,
        },
    },
});
const Layout = () => {
    return (
        <AppProvider
            router={router}
            theme={demoTheme}
            window={demoWindow}
        >
        <DashboardLayout hideNavigation
         slots={{
             appTitle: CustomAppTitle,
         }}>
            <Outlet />
        </DashboardLayout>
        </AppProvider>
    );
};

export default Layout;
