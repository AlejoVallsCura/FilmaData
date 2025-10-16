// utils.js (frontend puro)

export function slugify(str) {
  return String(str)
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)+/g, '');
}

export function nowISO() {
  return new Date().toISOString();
}

export async function fetchJSON(url,opt={}){ const r=await fetch(url,opt); let d=null; try{d=await r.json()}catch{} if(!r.ok){throw new Error((d&& (d.error||d.message))||`HTTP ${r.status}`)} return d;}
