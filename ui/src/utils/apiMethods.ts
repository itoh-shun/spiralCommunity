import { apiClient, ApiResponse } from './apiClient';

// GETリクエスト
export const get = async <T>(path: string, body?: any, options?: RequestInit): Promise<ApiResponse<T>> => {
    return await apiClient<T>( path, 'GET', body, options );
};

// POSTリクエスト
export const post = async <T>(path: string, body?: any, options?: RequestInit): Promise<ApiResponse<T>> => {
    return await apiClient<T>( path, 'POST', body, options );
};

// PUTリクエスト
export const put = async <T>(path: string, body?: any, options?: RequestInit): Promise<ApiResponse<T>> => {
    return await apiClient<T>( path, 'PUT', body, options );
};

// PATCHリクエスト
export const patch = async <T>(path: string, body?: any, options?: RequestInit): Promise<ApiResponse<T>> => {
    return await apiClient<T>( path, 'PATCH', body, options );
};

// DELETEリクエスト
export const del = async <T>(path: string, options?: RequestInit): Promise<ApiResponse<T>> => {
    return await apiClient<T>( path, 'DELETE', undefined, options );
};
