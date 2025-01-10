// src/components/Layout.js
import {CloudCircle} from "@mui/icons-material";
import {Chip, Container, Stack, Typography} from "@mui/material";
import {DashboardLayout} from "@toolpad/core";
import {Outlet} from 'react-router-dom';


function CustomAppTitle() {
    return (
        <Stack direction="row" alignItems="center" spacing={2}>
            <CloudCircle fontSize="large" color="primary"/>
            <Typography variant="h6">Spiral Community</Typography>
            <Chip size="small" label="BETA" color="info"/>
        </Stack>
    );
}

const Layout = () => {
    return (
        <DashboardLayout hideNavigation
                         slotProps={{
                             toolbarAccount: {
                                 slotProps: {
                                     signInButton: {
                                         sx: {
                                             display: "none"
                                         }
                                     },
                                 },
                             }
                         }}
                         slots={{
                             appTitle: CustomAppTitle,
                         }}>
            <Container sx={{my: 2}}>
                <Outlet/>
            </Container>
        </DashboardLayout>
    );
};

export default Layout;
