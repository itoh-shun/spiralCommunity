// src/pages/Home.js
import React from 'react';
import { Typography } from '@mui/material';

const Home = () => {
    return (
        <div>
            <Typography variant="h4" gutterBottom>
                ホームページ
            </Typography>
            <Typography variant="body1">
                これはホームページの内容です。
            </Typography>
        </div>
    );
};

export default Home;
