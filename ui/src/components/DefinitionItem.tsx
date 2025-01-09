import { Divider, Stack, Typography } from "@mui/material";
import React from "react";

export const DefinitionItem = ({ label, children }: {
    label: React.ReactNode;
    children: React.ReactNode;
}) => (
    <Stack direction="row" width="100%" py={ 2 } gap={ 2 }>
        <Typography component="dt" variant="body2" sx={ { flexGrow: 1 } } color={ "textSecondary" }>
            { label }
        </Typography>
        <Typography component="dd" variant="body1">
            { children }
        </Typography>
    </Stack>
);

export const DefinitionList = ({ children }: { children: React.ReactNode }) => (
    <Stack component="dl" divider={ <Divider/> }>
        { children }
    </Stack>
);