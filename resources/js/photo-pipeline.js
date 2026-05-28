/**
 * Pipeline photo client-side : HEIC detect → conversion lazy → EXIF rotation → Canvas JPEG 0.80 max 2048px.
 * D-43, D-44, Pitfall 8 — file.type ment sur iOS Safari, magic bytes obligatoires.
 * WebP interdit — régressions Safari iOS 17 (D-44).
 */

const MAX_EDGE = 2048;
const JPEG_QUALITY = 0.80;

/**
 * Détecte HEIC/HEIF par magic bytes (ftyp box brand).
 * Bytes 4-7 = 'ftyp', bytes 8-11 = major brand.
 * @param {File|Blob} file
 * @returns {Promise<boolean>}
 */
export async function isHeicByMagicBytes(file) {
    const buf = await file.slice(0, 12).arrayBuffer();
    const bytes = new Uint8Array(buf);
    // Vérifier ftyp box (offset 4-7)
    const ftyp = String.fromCharCode(bytes[4], bytes[5], bytes[6], bytes[7]);
    if (ftyp !== 'ftyp') return false;
    // Major brand (offset 8-11)
    const brand = String.fromCharCode(bytes[8], bytes[9], bytes[10], bytes[11]);
    return ['heic', 'heix', 'heis', 'hevc', 'mif1', 'msf1'].includes(brand);
}

/**
 * Pipeline complet : retourne un Blob JPEG compressé max 2048px.
 * Étapes : HEIC detection (magic bytes) → heic2any lazy → EXIF orientation → Canvas resize → JPEG.
 * @param {File} file
 * @returns {Promise<Blob>}
 */
export async function processPhoto(file) {
    let blob = file;

    // 1. Détecter HEIC par magic bytes (Pitfall 8 — file.type ment sur iOS Safari)
    if (await isHeicByMagicBytes(file)) {
        const { default: heic2any } = await import('heic2any');
        const converted = await heic2any({
            blob: file,
            toType: 'image/jpeg',
            quality: 0.85,
        });
        blob = Array.isArray(converted) ? converted[0] : converted;
    }

    // 2. Lire l'orientation EXIF AVANT drawImage (D-44 — évite les photos penchées)
    let orientation = 1;
    try {
        const { default: exifr } = await import('exifr');
        orientation = (await exifr.orientation(blob)) || 1;
    } catch {
        orientation = 1;
    }

    // 3. createImageBitmap (imageOrientation: 'from-image' applique l'EXIF nativement si supporté)
    let imageBitmap;
    try {
        imageBitmap = await createImageBitmap(blob, { imageOrientation: 'from-image' });
        // Si le browser a appliqué EXIF, reset orientation pour éviter double-transformation
        orientation = 1;
    } catch {
        imageBitmap = await createImageBitmap(blob);
    }

    // 4. Canvas resize max 2048px (D-44)
    const needsRotation = [5, 6, 7, 8].includes(orientation);
    const srcW = imageBitmap.width;
    const srcH = imageBitmap.height;
    const scale = Math.min(1, MAX_EDGE / Math.max(srcW, srcH));
    const dstW = Math.round(srcW * scale);
    const dstH = Math.round(srcH * scale);

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    if (needsRotation) {
        // Orientations 5-8 impliquent une rotation 90°/270° : inverser w/h du canvas
        canvas.width = dstH;
        canvas.height = dstW;
        ctx.save();
        applyExifTransform(ctx, orientation, dstW, dstH);
        ctx.drawImage(imageBitmap, 0, 0, dstW, dstH);
        ctx.restore();
    } else if (orientation !== 1) {
        canvas.width = dstW;
        canvas.height = dstH;
        ctx.save();
        applyExifTransform(ctx, orientation, dstW, dstH);
        ctx.drawImage(imageBitmap, 0, 0, dstW, dstH);
        ctx.restore();
    } else {
        canvas.width = dstW;
        canvas.height = dstH;
        ctx.drawImage(imageBitmap, 0, 0, dstW, dstH);
    }

    imageBitmap.close?.();

    return new Promise((resolve, reject) => {
        canvas.toBlob(
            (b) => (b ? resolve(b) : reject(new Error('canvas.toBlob retourné null'))),
            'image/jpeg',
            JPEG_QUALITY,
        );
    });
}

/**
 * Applique une transformation EXIF orientation 1-8 sur un contexte Canvas 2D.
 * Doit être appelé après ctx.save() et avant ctx.drawImage().
 * @param {CanvasRenderingContext2D} ctx
 * @param {number} orientation - valeur EXIF 1-8
 * @param {number} w - largeur de la source (avant rotation)
 * @param {number} h - hauteur de la source (avant rotation)
 */
function applyExifTransform(ctx, orientation, w, h) {
    switch (orientation) {
        case 2: ctx.translate(w, 0); ctx.scale(-1, 1); break;
        case 3: ctx.translate(w, h); ctx.rotate(Math.PI); break;
        case 4: ctx.translate(0, h); ctx.scale(1, -1); break;
        case 5: ctx.rotate(0.5 * Math.PI); ctx.scale(1, -1); break;
        case 6: ctx.rotate(0.5 * Math.PI); ctx.translate(0, -h); break;
        case 7: ctx.rotate(0.5 * Math.PI); ctx.translate(w, -h); ctx.scale(-1, 1); break;
        case 8: ctx.rotate(-0.5 * Math.PI); ctx.translate(-w, 0); break;
        default: break;
    }
}
