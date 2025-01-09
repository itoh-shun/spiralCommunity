export interface ApiResponse<T> {
    data: T | null;
    error: string | null;
}

// @ts-ignore
const { API_URL: BASE_URL } = window.__GLOBAL_CONST__;

/**
 * 汎用的なAPIクライアント
 * @param path - APIのエンドポイント
 * @param method - HTTPメソッド (GET, POST, etc.)
 * @param body - リクエストボディ (POST, PUT, PATCHの場合)
 * @param options - その他のオプション (ヘッダーなど)
 * @returns - APIのレスポンスデータとエラーメッセージ
 */
export const apiClient = async <T>(
    path: string,
    method: string,
    body?: any,
    options?: RequestInit
): Promise<ApiResponse<T>> => {
    try {
        const url = new URL( BASE_URL );
        url.searchParams.set( '_path', path );
        url.searchParams.set( '_method', method );

        if( method === 'GET' ) {
            Object.keys( body ).forEach( key => {
                url.searchParams.append( key, String( body[key] ) );
            } );
        }

        const reqMethod = method.toUpperCase() === 'GET' ? 'get' : 'post';

        const encodeParams = (data: Record<string, any>): string => {
            return Object.entries( data )
                .map( ([ key, value ]) =>
                    `${ encodeURIComponent( key ) }=${ encodeURIComponent( value ) }`
                )
                .join( '&' ); // `key1=value1&key2=value2`形式を構築
        };

        const fetchOptions: RequestInit = {
            method: reqMethod,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded', // form-urlencoded型で送信
            },
            body:
                method.toUpperCase() !== 'GET' && body
                    ? encodeParams( body )
                    : undefined, // GETの場合は body を含めない
            ...options,
        };

        const response = await fetch( url.toString(), fetchOptions );

        if( !response.ok ) {
            return {
                data: null,
                error: `Error: ${ response.status } ${ response.statusText }`,
            };
        }

        const data: T = await response.json();
        return { data, error: null };
    } catch( error ) {
        return {
            data: null,
            error: error instanceof Error ? error.message : 'Unknown error occurred',
        };
    }
};
