import {AdminPanelSettings, Delete, LocalPoliceTwoTone, LocationOn, MoreVert} from "@mui/icons-material";
import {
    Avatar,
    Badge,
    Divider,
    IconButton,
    Link,
    List,
    ListItem,
    ListItemAvatar,
    ListItemButton,
    ListItemIcon,
    ListItemText,
    Menu,
    MenuItem,
    Paper,
    Stack,
    TablePagination,
    Typography
} from '@mui/material';
import Breadcrumbs from "@mui/material/Breadcrumbs";
import {useSession} from "@toolpad/core";
import React, {useEffect, useState} from "react";
import {useNavigate} from "react-router-dom";
import useSWR from "swr";
import {UserSession} from "../App.tsx";
import {PaginationResponse, User} from "../types/api/fetchUsers.ts";
import {get} from "../utils/apiMethods.ts";
import {base64ToTemporaryURL} from "../utils/base64ToTemporaryURL.ts";

const MoreVertElem = (param: {
    permission: 'Admin' | 'User'
}) => {
    const [anchorEl, setAnchorEl] = React.useState<null | HTMLElement>(null);
    const open = Boolean(anchorEl);
    const session = useSession<UserSession>()
    const handleClick = (event: React.MouseEvent<HTMLElement>) => {
        setAnchorEl(event.currentTarget);
    };
    const handleClose = () => {
        setAnchorEl(null);
    };
    return (
        <>
            <IconButton
                aria-haspopup="true"
                aria-expanded={open ? 'true' : undefined}
                onClick={handleClick}
            >
                <MoreVert/>
            </IconButton>
            <Menu
                anchorEl={anchorEl}
                open={open}
                onClose={handleClose}
                anchorOrigin={{
                    vertical: 'top',
                    horizontal: 'left',
                }}
                transformOrigin={{
                    vertical: 'top',
                    horizontal: 'left',
                }}
            >
                <MenuItem onClick={handleClose}>
                    <ListItemIcon>
                        <AdminPanelSettings fontSize="small"/>
                    </ListItemIcon>
                    {param.permission === 'Admin' ? '一般に変更' : '管理者に変更'}
                </MenuItem>
                <Divider/>
                <MenuItem onClick={handleClose} color={'error'} disabled={session?.user?.permission !== 'Admin'}>
                    <ListItemIcon>
                        <Delete fontSize="small"/>
                    </ListItemIcon>
                    ユーザーを削除
                </MenuItem>
            </Menu>
        </>
    )
}

const Users = () => {
    const [users, setUsers] = useState<User[]>([]);
    const [page, setPage] = React.useState(0);
    const [rowsPerPage, setRowsPerPage] = React.useState(10);
    const [count, setCount] = React.useState(0);
    const navigate = useNavigate()

    const {data} = useSWR(
        ["api/users", page, rowsPerPage],
        async ([url, page, limit]) => {
            // ここで fetch or get などを行う
            const response = await get<PaginationResponse<User>>(url, {page, limit});
            return response.data;
        }
    );

    // data が取得できたら setUsers, setCount を更新
    useEffect(() => {
        if (data) {
            setUsers(
                data.items.map((user) => ({
                    ...user,
                    userImage: base64ToTemporaryURL(user.userImage),
                }))
            );
            setCount(data.count);
        }
    }, [data]);

    const handleChangePage = (_event: unknown, newPage: number) => {
        setPage(newPage);
    };


    const handleChangeRowsPerPage = (value: string) => {
        setRowsPerPage(+value);
        setPage(0);
    };

    return (
        <div>
            <Breadcrumbs sx={{my: 2}}>
                <Link underline="hover" color="inherit"
                      onClick={() => navigate('/')}>
                    Home
                </Link>
                <Typography sx={{color: 'text.primary'}}>ユーザーリスト</Typography>
            </Breadcrumbs>
            <Typography variant="h4" gutterBottom>
                ユーザーリスト
            </Typography>
            <Paper sx={{width: '100%', maxWidth: '750px', overflow: 'hidden', mx: 'auto'}} elevation={2}>
                <List sx={{width: '100%'}}>
                    {users.map((user, index) =>
                        <ListItem secondaryAction={
                            <>
                                <MoreVertElem permission={user.permission}/>
                            </>
                        } key={index} divider disablePadding>
                            <ListItemButton dense onClick={() => {
                                navigate(`/users/${user.id}`)
                            }}>
                                <ListItemAvatar>
                                    <Badge
                                        overlap="circular"
                                        invisible={user.permission !== 'Admin'}
                                        anchorOrigin={{vertical: 'bottom', horizontal: 'right'}}
                                        badgeContent={
                                            <LocalPoliceTwoTone color={"primary"}/>
                                        }
                                    >
                                        <Avatar src={user.userImage}/>
                                    </Badge>
                                </ListItemAvatar>
                                <Stack sx={{flex: "1 1 auto"}}>
                                    <ListItemText primary={user.name} secondary={user.email}/>
                                    {user.office_location && <Stack direction={'row'}>
                                        <LocationOn fontSize={'small'}/><Typography variant={'caption'}
                                                                                    color={"textSecondary"}
                                                                                    mt={0.5}
                                                                                    mx={0.5}>{user.office_location}</Typography>
                                    </Stack>}
                                </Stack>
                            </ListItemButton>
                        </ListItem>
                    )}
                </List>
                <TablePagination
                    labelRowsPerPage={"件数表示"}
                    rowsPerPageOptions={[1, 10, 25, 100]}
                    component="div"
                    count={count}
                    rowsPerPage={rowsPerPage}
                    page={page}
                    onPageChange={handleChangePage}
                    onRowsPerPageChange={(event) => handleChangeRowsPerPage(event.target.value)}
                />
            </Paper>
        </div>
    );
};

export default Users;