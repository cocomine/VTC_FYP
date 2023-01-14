/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

declare module 'media-select' {
    export function select_media(selected_media: (ids: string[]) => void, max?: number, mime?: RegExp):void
}