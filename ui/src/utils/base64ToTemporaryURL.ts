export const base64ToTemporaryURL = (base64Data: string, contentType: string = "application/octet-stream") => {
    const binaryData = atob(base64Data); // Base64をデコード
    const byteArray = new Uint8Array(binaryData.length);

    for (let i = 0; i < binaryData.length; i++) {
        byteArray[i] = binaryData.charCodeAt(i);
    }

    const blob = new Blob([byteArray], { type: contentType });
    return URL.createObjectURL(blob); // 一時URLを生成
};