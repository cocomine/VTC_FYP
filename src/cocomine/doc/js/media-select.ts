/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

declare module 'media-select' {
    export function select_media(selected_media: (ids: {id: string, name: string}) => void, max?: number, mime?: RegExp):void;
    export type data = {S
        jq_modal: JQuery<HTMLElement>,
        filter_mime: RegExp
    }
}