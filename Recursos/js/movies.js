// movies.js (sin backend: guarda en localStorage)
import { slugify, nowISO } from './utils.js';

const KEY = 'filmdatamovies:v1';

function readStore() {
  try { return JSON.parse(localStorage.getItem(KEY)) || []; }
  catch { return []; }
}
function writeStore(arr) {
  localStorage.setItem(KEY, JSON.stringify(arr));
}

export function listMovies() {
  return readStore();
}

export function getMovieBySlug(slug) {
  return readStore().find(m => m.slug === slug) || null;
}

export function saveMovie({ title, genre, year = '', country = '', review, image = '' }) {
  const store = readStore();

  // slug único
  let base = slugify(title);
  let slug = base;
  let i = 2;
  while (store.some(m => m.slug === slug)) slug = `${base}-${i++}`;

  const movie = {
    id: crypto.randomUUID(),
    slug,
    title,
    genre,
    year: year ? Number(year) : null,
    country,
    review,
    image,                 // URL o DataURL
    createdAt: nowISO()
  };

  store.push(movie);
  writeStore(store);
  return movie;
}


import { fetchJSON } from './utils.js';
export async function saveMovie(fd){ return await fetchJSON('../api/save_movie.php',{method:'POST',body:fd}); }
export async function listMovies(){ try{ const a=await fetchJSON('../data/movies.json?ts='+Date.now()); return Array.isArray(a)?a:[] }catch{ return [] } }
export async function getMovieBySlug(slug){
  try{ const m=await fetchJSON('../api/get_movie.php?slug='+encodeURIComponent(slug)); if(m) return m; }catch{}
  const all=await listMovies(); return all.find(x=>x.slug===slug)||null;
}
