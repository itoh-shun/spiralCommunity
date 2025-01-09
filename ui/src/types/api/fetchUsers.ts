import { Tag } from "./fetchTags.ts";

export type User = {
    id: number;
    name: string;
    email: string;
    userImage: string;
    permission: 'Admin' | 'User';
    office_location: string,
}

export type PaginationResponse<T> = {
    items: T[],
    count: number,
    page: number,
    limit: number
}

export type UserInfoType = {
    id: number;
    name: string;
    email: string;
    userImage: string;
    permission: 'Admin' | 'User';
    office_location: string,
    birthday: string,
    birthplace: string,
    gender: "male" | "female" | "other" | '',
    joined: string,
    tags: Tag[],
    memo: string,
}

export type ValidateError = {
    field: string;
    isValid: string;
    message: string;
    value: string;
}