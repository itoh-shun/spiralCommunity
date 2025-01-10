import {CheckBox, CheckBoxOutlineBlank, Edit, LocalPoliceTwoTone, LocationOn} from "@mui/icons-material";
import {LoadingButton} from "@mui/lab";
import {
    Autocomplete,
    Avatar,
    Badge,
    Box,
    Button,
    Card,
    CardContent,
    Checkbox,
    Chip,
    CircularProgress,
    Dialog,
    DialogActions,
    DialogContent,
    DialogTitle,
    Divider,
    FormControl,
    FormHelperText,
    IconButton,
    InputLabel,
    Link,
    MenuItem,
    Paper,
    Select,
    Stack,
    styled,
    TextField,
    Typography,
} from '@mui/material';
import Breadcrumbs from '@mui/material/Breadcrumbs';
import Grid from '@mui/material/Grid2';
import {DesktopDatePicker} from "@mui/x-date-pickers";
import {DialogProps, useDialogs} from "@toolpad/core";
import {Fragment, useEffect, useState} from "react";
import {Controller, ControllerRenderProps, SubmitHandler, useForm} from "react-hook-form";
import {useNavigate, useParams} from "react-router-dom";
import useSWR from "swr";
import useSWRMutation from "swr/mutation";
import {DefinitionItem, DefinitionList} from "../components/DefinitionItem.tsx";
import {Tag} from "../types/api/fetchTags.ts";
import {UserInfoType, ValidateError} from "../types/api/fetchUsers.ts";
import {get, patch} from "../utils/apiMethods.ts";
import {base64ToTemporaryURL} from "../utils/base64ToTemporaryURL.ts";
import SimpleMdeReact from "react-simplemde-editor";
import {useTheme} from "@mui/material/styles";
import {marked} from "marked";
import DOMPurify from 'dompurify';

type FixedTagsProps = {
    field: ControllerRenderProps<FormInputType, "tags">;
    error?: boolean,
    helperText?: string | null,
};

function FixedTags({field, error, helperText}: FixedTagsProps) {
    const [tags, setTags] = useState<Tag[]>([]);
    const icon = <CheckBoxOutlineBlank fontSize="small"/>;
    const checkedIcon = <CheckBox fontSize="small"/>;

    const {data, isLoading} = useSWR<Tag[] | null, Error, [string]>(
        [`api/tags`],
        async ([url]) => {
            const response = await get<Tag[]>(url, {});
            return response.data;
        }
    );

    // data が取得できたら setUsers, setCount を更新
    useEffect(() => {
        if (data) {
            setTags(data);
        }
    }, [data]);

    return (
        <Autocomplete
            multiple
            // field.value が現在の選択値
            value={field.value ?? []}
            // Autocomplete で変更があったら react-hook-form に通知
            onChange={(_event, newValue) => {
                field.onChange(newValue);
            }}
            disableCloseOnSelect
            fullWidth
            options={tags}
            groupBy={(option) => option.category}
            getOptionLabel={(option) => option.name}
            loading={isLoading}
            isOptionEqualToValue={(option, value) => option.name === value.name}
            renderOption={(props, option, {selected}) => {
                const {key, ...optionProps} = props;
                return (
                    <li key={key} {...optionProps}>
                        <Checkbox
                            icon={icon}
                            checkedIcon={checkedIcon}
                            style={{marginRight: 8}}
                            checked={selected}
                        />
                        {option.name}
                    </li>
                );
            }}
            renderTags={(tagValue, getTagProps) =>
                tagValue.map((option, index) => {
                    const {key, ...tagProps} = getTagProps({index});
                    return (
                        <Chip
                            key={key}
                            label={option.name}
                            {...tagProps}
                        />
                    );
                })
            }
            renderInput={(params) => (
                <TextField {...params} label="興味タグ" variant="outlined"
                           fullWidth
                           error={error}
                           helperText={helperText}
                           slotProps={{
                               input: {
                                   ...params.InputProps,
                                   endAdornment: (
                                       <Fragment>
                                           {isLoading ? <CircularProgress color="inherit" size={20}/> : null}
                                           {params.InputProps.endAdornment}
                                       </Fragment>
                                   ),
                               },
                           }}/>
            )}
        />
    );
}

const Item = styled(Button)(({theme}) => ({
    paddingY: theme.spacing(4),
    flexDirection: 'column',
}));

type FormInputType = {
    birthday: Date | string | null,
    gender: "male" | "female" | "other" | '',
    tags: Tag[] | null,
    birthplace: Date | string | null,
    joined: Date | string | null,
};

type FormErrorType = {
    birthday: string | null,
    gender: string | null,
    tags: string | null,
    birthplace: string | null,
    joined: string | null,
};

type FormMemoInputType = {
    memo: string | null,
}

type FormMemoErrorType = {
    memo: string | null,
}

function EditMemo({payload: user, open, onClose}: DialogProps<UserInfoType, null>) {
    const [errors, setErrors] = useState<FormMemoErrorType>();

    const [markdown, setMarkdown] = useState(user.memo ?? '');

    const {trigger, isMutating} = useSWRMutation(
        `api/users/${user.id}/memo`, // Key as a string
        async (url: string, {arg}: { arg: FormMemoInputType }) => {
            const response = await patch<
                {
                    result: {
                        memo: ValidateError,
                    },
                    error: boolean,
                }>(url, arg);
            return response.data;
        }
    );

    const onSubmit: SubmitHandler<FormMemoInputType> = async (data) => {
        if (data) {
            try {
                await trigger({
                    ...data,
                }, {
                    onSuccess: (res) => {
                        if (res?.error) {
                            setErrors({
                                memo: res.result.memo.message,
                            })
                        } else {
                            onClose(null);
                        }
                    }
                });
            } catch (error) {
                console.error("Failed to update profile:", error);
                // Optionally, handle the error (e.g., show a notification)
            }
        }
    };

    const theme = useTheme();

    // Update form values when user prop changes
    useEffect(() => {
        if (user) {
            setMarkdown(user.memo ?? '');
        }
    }, [user]);

    return (
        <Dialog
            fullScreen
            open={open}
        >
            <DialogTitle>自己紹介の編集</DialogTitle>
            <DialogContent>
                <Stack
                    sx={{
                        ...(theme.palette.mode === 'dark' && {
                            '& .CodeMirror': {
                                color: theme.palette.common.white,
                                borderColor: theme.palette.background.paper,
                                backgroundColor: 'inherit',
                            },
                            '& .cm-s-easymde .CodeMirror-cursor': {
                                borderColor: theme.palette.background.paper,
                            },
                            '& .editor-toolbar > *': {
                                color: theme.palette.common.white,
                            },
                            '& .editor-toolbar > .active, & .editor-toolbar > button:hover, & .editor-preview pre, & .cm-s-easymde .cm-comment': {
                                backgroundColor: theme.palette.background.paper,
                            },
                            '& .editor-preview': {
                                backgroundColor: theme.palette.background.default,
                            },
                            '& .editor-toolbar.fullscreen, & .EasyMDEContainer .CodeMirror-fullscreen': {
                                backgroundColor: theme.palette.background.paper,
                            }
                        }),
                        mt: 1,
                    }} gap={2}>
                    <Typography>
                        自己紹介を編集します。入力をしたら保存ボタンを押してください
                    </Typography>
                    <SimpleMdeReact value={markdown} onChange={(e) => setMarkdown(e)}/>
                    <FormHelperText error={!!errors?.memo}>{errors?.memo}</FormHelperText>
                </Stack>
            </DialogContent>
            <DialogActions>
                <LoadingButton loading={isMutating}
                               onClick={() => onSubmit({memo: markdown})}
                               type="submit" variant="contained" color="primary">
                    保存
                </LoadingButton>
                <Button onClick={() => onClose(null)} variant="text" color="secondary">
                    キャンセル
                </Button>
            </DialogActions>
        </Dialog>
    );
}

function EditProfile({payload: user, open, onClose}: DialogProps<UserInfoType, null>) {
    const {control, handleSubmit, reset} = useForm<FormInputType>();
    const [errors, setErrors] = useState<FormErrorType>();

    const {trigger, isMutating} = useSWRMutation(
        `api/users/${user.id}`, // Key as a string
        async (url: string, {arg}: { arg: Omit<FormInputType, 'tags'> & { tags: string | null } }) => {
            const response = await patch<
                {
                    result: {
                        birthday: ValidateError,
                        gender: ValidateError,
                        birthplace: ValidateError,
                        joined: ValidateError,
                        tags: ValidateError,
                    },
                    error: boolean,
                }>(url, arg);
            return response.data;
        }
    );

    const onSubmit: SubmitHandler<FormInputType> = async (data) => {
        if (data) {
            try {
                await trigger({
                    ...data,
                    tags: data.tags ? JSON.stringify(data.tags?.map((tag: Tag) => tag?.id)) : JSON.stringify([]),
                    birthday: (data.birthday instanceof Date) ? data.birthday?.toISOString().split('T')[0] : data.birthday,
                    joined: (data.joined instanceof Date) ? data.joined?.toISOString().split('T')[0] : data.joined,
                }, {
                    onSuccess: (res) => {
                        if (res?.error) {
                            setErrors({
                                birthday: res.result.birthday.message,
                                gender: res.result.gender.message,
                                birthplace: res.result.birthplace.message,
                                joined: res.result.joined.message,
                                tags: res.result.tags.message,
                            })
                            console.log(errors);
                        } else {
                            onClose(null);
                        }
                    }
                });
            } catch (error) {
                console.error("Failed to update profile:", error);
                // Optionally, handle the error (e.g., show a notification)
            }
        }
    };


    // Update form values when user prop changes
    useEffect(() => {
        if (user) {
            reset({
                birthday: user.birthday && new Date(user.birthday),
                birthplace: user.birthplace,
                gender: user.gender,
                joined: user.joined && new Date(user.joined),
                tags: user.tags ?? [],
            });
        }
    }, [user, reset]);

    return (
        <Dialog
            fullWidth
            open={open}
            PaperProps={{
                component: 'form',
                onSubmit: handleSubmit(onSubmit),
            }}
        >
            <DialogTitle>プロフィール編集</DialogTitle>
            <DialogContent>
                <Stack sx={{mt: 1}} gap={2}>
                    <Typography>
                        プロフィールを編集します。入力をしたら保存ボタンを押してください
                    </Typography>

                    {/* Birthday Picker */}
                    <Controller
                        name="birthday"
                        control={control}
                        render={({field}) => (
                            <DesktopDatePicker
                                {...field}
                                label="誕生日"
                                slotProps={{
                                    textField: {
                                        helperText: errors?.birthday,
                                        error: !!errors?.birthday,
                                    },
                                }}
                            />
                        )}
                    />

                    {/* Birthday Picker */}
                    <Controller
                        name="birthplace"
                        control={control}
                        render={({field}) => (
                            <TextField
                                {...field}
                                label="出身地"
                                error={!!errors?.birthplace}
                                helperText={errors?.birthplace}
                            />
                        )}
                    />
                    {/* Gender Select */}
                    <FormControl fullWidth
                                 error={!!errors?.gender}>
                        <InputLabel>性別</InputLabel>
                        <Controller
                            name="gender"
                            control={control}
                            render={({field}) => (
                                <Select
                                    {...field}
                                    label="性別"
                                >
                                    <MenuItem value="female">女性</MenuItem>
                                    <MenuItem value="male">男性</MenuItem>
                                    <MenuItem value="other">その他</MenuItem>
                                </Select>
                            )}
                        />
                        <FormHelperText>{errors?.gender}</FormHelperText>
                    </FormControl>

                    {/* Joined Date Picker */}
                    <Controller
                        name="joined"
                        control={control}
                        render={({field}) => (
                            <DesktopDatePicker
                                {...field}
                                label="入社日"
                                slotProps={{
                                    textField: {
                                        helperText: errors?.joined,
                                        error: !!errors?.joined,
                                    },
                                }}
                            />
                        )}
                    />

                    {/* Tags Input */}
                    <Controller
                        name="tags"
                        control={control}
                        render={({field}) => <FixedTags field={field}
                                                        error={!!errors?.tags}
                                                        helperText={errors?.tags}
                        />}
                    />
                </Stack>
            </DialogContent>
            <DialogActions>
                <LoadingButton loading={isMutating} type="submit" variant="contained" color="primary">
                    保存
                </LoadingButton>
                <Button onClick={() => onClose(null)} variant="text" color="secondary">
                    キャンセル
                </Button>
            </DialogActions>
        </Dialog>
    );
}


const UserInfo = () => {

    const {userId} = useParams()
    const dialogs = useDialogs();
    const [user, setUser] = useState<UserInfoType | null>(null);
    const theme = useTheme();
    const {data, mutate} = useSWR<UserInfoType | null, Error, [string]>(
        [`api/users/${userId}`],
        async ([url]) => {
            const response = await get<UserInfoType>(url, {});
            return response.data;
        }
    );

    const navigate = useNavigate();
    const rawHtml = user?.memo ? marked(user.memo) : null;
    const sanitizedHtml: string = rawHtml ? DOMPurify.sanitize(rawHtml as string) : '';

    // data が取得できたら setUsers, setCount を更新
    useEffect(() => {
        if (data) {
            setUser({
                ...data,
                userImage: base64ToTemporaryURL(data?.userImage ?? ''),
            });
        }
    }, [data]);

    async function handleDialogClose() {
        await mutate();
    }

    return (
        <div>
            <Breadcrumbs sx={{my: 2}}>
                <Link underline="hover" color="inherit"
                      onClick={() => navigate('/')}>
                    Home
                </Link>
                <Link
                    underline="hover"
                    color="inherit"
                    onClick={() => navigate('/users')}
                >
                    ユーザーリスト
                </Link>
                <Typography sx={{color: 'text.primary'}}>{user?.name}</Typography>
            </Breadcrumbs>
            <Paper sx={{width: '100%', overflow: 'hidden', mx: 'auto', p: 4}}>
                <Stack alignItems={"center"}>
                    <Badge
                        overlap="circular"
                        invisible={user?.permission !== 'Admin'}
                        anchorOrigin={{vertical: 'bottom', horizontal: 'right'}}
                        badgeContent={
                            <LocalPoliceTwoTone fontSize={"large"} color={"primary"}/>
                        }

                    >
                        <Avatar src={user?.userImage} sx={{width: 87, height: 87}}/>
                    </Badge>
                </Stack>
                <Stack alignItems={"center"} mt={1}>
                    <Typography variant="subtitle2">{user?.name}</Typography>
                    <Typography variant="subtitle2" color={'textSecondary'}>{user?.email}</Typography>
                </Stack>
                {user?.office_location &&
                    <Stack alignItems={"center"}
                           justifyContent={"center"} direction={'row'} mt={1}>
                        <LocationOn fontSize={'small'}/>
                        <Typography variant={'caption'} color={"textSecondary"}
                                    mt={0.5}
                                    mx={0.5}>{user?.office_location}</Typography>
                    </Stack>
                }
                <Stack
                    direction="row"
                    divider={<Divider orientation="vertical" flexItem/>}
                    spacing={2}
                    mt={2}
                    justifyContent={"center"}
                >
                    <Item>
                        <Typography variant="body1" color={"textPrimary"}>100,000</Typography>
                        <Typography variant="body1" color={"textSecondary"}
                                    fontSize={'small'}>Follow</Typography>
                    </Item>
                    <Item>
                        <Typography variant="body1" color={"textPrimary"}>100,000</Typography>
                        <Typography variant="body1" color={"textSecondary"}
                                    fontSize={'small'}>Follower</Typography>
                    </Item>
                </Stack>
            </Paper>
            <Box>
                <Grid container spacing={2}
                      direction={{xs: "column-reverse", lg: "row"}}>
                    <Grid size={{xs: 12, lg: 4}}>
                        <Card sx={{mt: 2, position: 'relative'}}>
                            <IconButton size={"small"}
                                        sx={{
                                            position: 'absolute',
                                            top: '0.75rem',
                                            right: '0.75rem'
                                        }}
                                        onClick={async () => {
                                            if (!user) {
                                                return
                                            }
                                            await dialogs.open<UserInfoType, null>(EditProfile, user, {
                                                onClose: handleDialogClose
                                            });
                                        }}><Edit/></IconButton>
                            <CardContent>
                                <Typography variant={"subtitle1"}>プロフィール</Typography>
                                <DefinitionList>
                                    <DefinitionItem label="誕生日">{user?.birthday}</DefinitionItem>
                                    <DefinitionItem
                                        label="性別">{user?.gender === 'male' ? '男性' : (user?.gender === 'female') ? '女性' : user?.gender === 'other' ? 'その他' : ""}</DefinitionItem>
                                    <DefinitionItem label="入社日">{user?.joined}</DefinitionItem>
                                    <DefinitionItem label="出身地">{user?.birthplace}</DefinitionItem>
                                    <DefinitionItem label="興味タグ">
                                        <Stack direction={'row'} gap={1} flexWrap={"wrap"} justifyContent={'right'}>
                                            {user?.tags.map((tag, index) => <Chip
                                                size={"small"}
                                                label={tag.name}
                                                key={index}
                                            />)}
                                        </Stack>
                                    </DefinitionItem>
                                </DefinitionList>
                            </CardContent>
                        </Card>
                    </Grid>
                    <Grid size={{xs: 12, lg: 8}}>
                        <Card sx={{mt: 2, position: 'relative'}}>
                            <IconButton size={"small"}
                                        sx={{
                                            position: 'absolute',
                                            top: '0.75rem',
                                            right: '0.75rem'
                                        }}
                                        onClick={async () => {
                                            if (!user) {
                                                return
                                            }
                                            await dialogs.open<UserInfoType, null>(EditMemo, user, {
                                                onClose: handleDialogClose
                                            })
                                        }}
                            ><Edit/></IconButton>
                            <CardContent>
                                <Typography variant={"subtitle1"}>自己紹介</Typography>
                                <Box // EasyMDEのプレビュースタイルに合わせたクラス名
                                    sx={{
                                        ...(theme.palette.mode === 'dark' && {
                                            '& .CodeMirror': {
                                                color: theme.palette.common.white,
                                                borderColor: theme.palette.background.paper,
                                                backgroundColor: 'inherit',
                                            },
                                            '& .cm-s-easymde .CodeMirror-cursor': {
                                                borderColor: theme.palette.background.paper,
                                            },
                                            '& .editor-toolbar > *': {
                                                color: theme.palette.common.white,
                                            },
                                            '& .editor-toolbar > .active, & .editor-toolbar > button:hover, & .editor-preview pre, & .cm-s-easymde .cm-comment': {
                                                backgroundColor: theme.palette.background.paper,
                                            },
                                            '& .editor-preview': {
                                                backgroundColor: "initial",
                                            },
                                            '& .editor-toolbar.fullscreen, & .EasyMDEContainer .CodeMirror-fullscreen': {
                                                backgroundColor: theme.palette.background.paper,
                                            }
                                        }),
                                    }}
                                >
                                    <div
                                        className="editor-preview"
                                        dangerouslySetInnerHTML={{__html: sanitizedHtml}}
                                    />
                                </Box>
                            </CardContent>
                        </Card>
                    </Grid>
                </Grid>
            </Box>

        </div>
    );
};

export default UserInfo;