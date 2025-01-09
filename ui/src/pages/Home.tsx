// src/pages/Home.js
import {Button, Typography} from '@mui/material';
import {useNavigate} from "react-router-dom";

const Home = () => {
    const navigate = useNavigate()
    return (
        <div>
            <Typography variant="h4" gutterBottom>
                ホームページ
            </Typography>
            <Typography variant="body1">
                これはホームページの内容です。
            </Typography>
            <Button onClick={() =>
                navigate('/users')
            }>ユーザーリスト</Button>
        </div>
    );
};

export default Home;
