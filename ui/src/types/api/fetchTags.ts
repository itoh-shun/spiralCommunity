import useSWR from "swr";
import { get } from "../../utils/apiMethods.ts";

export interface Tag {
    id: number;
    name: string;
    category: string;
}

export const useFetchTags = () => {
    const { data, error } = useSWR( [ `api/tags` ], get<Tag[]> );
    if( error ) {
        console.error( 'Error fetching tag:', error );
        return null;
    }
    return data;
};