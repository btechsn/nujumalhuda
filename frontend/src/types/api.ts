/**
 * Types TypeScript pour l'API Nujum Al-Huda
 * Générés depuis les API Resources Laravel
 */

// Types de base
export interface Timestamps {
  created_at: string;
  updated_at: string;
}

export interface I18nField {
  fr: string;
  en?: string;
  ar?: string;
}

// Education Module
export interface ProgramMoney {
  amount_minor?: number;
  amount?: number;
  currency?: string;
  formatted?: string;
}

export interface Program extends Timestamps {
  id: string;
  name: I18nField;
  description: I18nField;
  objectives?: Record<string, string[]> | string[] | null;
  type: 'coran' | 'arabe' | 'baye_niasse' | 'sunnite' | 'autre';
  level: 'debutant' | 'intermediaire' | 'avance';
  duration_weeks: number;
  hours_per_week: number;
  /** Présent si l'API aplatit les montants */
  tuition_amount?: string;
  registration_amount?: string;
  tuition?: ProgramMoney;
  registration?: ProgramMoney;
  currency: string;
  min_age?: number;
  max_age?: number;
  prerequisite_program_id?: string | null;
  prerequisite_program?: Program | null;
  promotions?: Promotion[];
  is_active: boolean;
  is_featured: boolean;
  display_order: number;
  metadata?: Record<string, unknown> | null;
}

export interface TeacherSanad {
  riwaya?: string;
  chain?: string[];
}

export interface TeacherIjaza {
  title?: string;
  issuer?: string;
  year?: string;
  domain?: string;
}

export interface Teacher extends Timestamps {
  id: string;
  user_id?: string;
  name: string;
  email?: string;
  title?: I18nField | null;
  bio?: I18nField;
  specialties?: Record<string, string[]> | I18nField;
  qualifications?: Record<string, string[]> | I18nField;
  has_ijaza: boolean;
  has_sanad?: boolean;
  sanad?: TeacherSanad | null;
  ijaza_details?: Record<string, TeacherIjaza> | null;
  photo_url?: string | null;
  stats?: {
    students_count?: number;
    courses_taught?: number;
    total_sessions?: number;
  };
  is_available: boolean;
  is_featured: boolean;
  display_order: number;
}

export interface Promotion extends Timestamps {
  id: string;
  program_id: string;
  name: I18nField;
  code: string;
  academic_year: number;
  start_date: string;
  end_date: string;
  capacity: number;
  enrolled_count: number;
  active_count?: number;
  min_students: number;
  fill_percent?: number;
  is_full?: boolean;
  can_enroll?: boolean;
  status: 'draft' | 'upcoming' | 'ongoing' | 'completed' | 'cancelled';
  is_open_for_enrollment: boolean;
  schedule?: Record<string, string> | null;
  location?: string | null;
  program?: Pick<Program, 'id' | 'name' | 'type' | 'level' | 'duration_weeks' | 'hours_per_week'> | null;
  main_teacher?: (Pick<Teacher, 'id' | 'name' | 'email' | 'photo_url' | 'title'> & {
    photo_url?: string | null;
  }) | null;
}

export interface Enrollment extends Timestamps {
  id: string;
  promotion_id: string;
  student_name: string;
  student_email: string;
  student_phone: string;
  date_of_birth?: string;
  status: 'pending' | 'approved' | 'rejected' | 'active' | 'completed' | 'withdrawn';
  promotion?: Promotion;
}

export interface QuizQuestion {
  prompt_i18n?: I18nField;
  prompt?: string;
  choices: Array<string | I18nField>;
  correct_index?: number;
}

export interface QuizSummary {
  id: string;
  slug: string;
  topic: string;
  title: I18nField;
  questions_count: number;
}

export interface QuizDetail extends QuizSummary {
  questions: QuizQuestion[];
}

export interface CertificateSummary {
  id: string;
  student_name: string;
  level: I18nField;
  milestone_slug?: string | null;
  verification_code: string;
  issued_at: string;
  document_url?: string | null;
  kind: string;
  is_ijaza: boolean;
}

export interface IjazaSummary {
  id: string;
  student_name: string;
  teacher_name: string;
  scope: I18nField;
  sanad?: I18nField | null;
  verification_code: string;
  signed_at: string;
  document_url?: string | null;
  kind: string;
  is_ijaza: boolean;
}

// Mosque Module
export interface PrayerTime {
  id: string;
  date: string;
  prayer_name: 'fajr' | 'dhuhr' | 'asr' | 'maghrib' | 'isha';
  calculated_time: string; // HH:mm
  manual_time?: string;
  is_overridden: boolean;
  iqama_time: string;
  display_time: string; // Le temps final affiché
}

export interface PrayerTimesResponse {
  date: string;
  hijri_date: string;
  is_jummah: boolean;
  next_prayer?: {
    name: string;
    time: string;
    remaining_minutes: number;
  };
  prayers: PrayerTime[];
}

export interface Khutba extends Timestamps {
  id: string;
  title: I18nField;
  summary?: I18nField;
  content?: I18nField;
  date: string;
  time: string;
  speaker?: { id?: string; name?: string };
  speaker_name?: string;
  key_points?: string;
  references?: Record<string, string>;
  audio_url?: string;
  video_url?: string;
  youtube_url?: string;
  is_published: boolean;
  published_at?: string;
  related_live?: {
    id: string;
    title?: string | null;
    status?: string | null;
    thumbnail_url?: string | null;
    scheduled_at?: string | null;
    recording?: { id: string; slug: string; status: string } | null;
  } | null;
}

export interface MosqueEvent extends Timestamps {
  id: string;
  title: I18nField;
  description: I18nField;
  type: 'lecture' | 'workshop' | 'fundraising' | 'special_prayer' | 'community' | 'gamou' | 'other';
  start_at: string;
  end_at: string;
  location?: string;
  capacity?: number;
  registered_count: number;
  requires_registration: boolean;
  is_recurring: boolean;
  status: 'draft' | 'upcoming' | 'ongoing' | 'completed' | 'cancelled';
  is_featured: boolean;
  speaker_name?: string;
}

// News Module
export interface ArticleCategory {
  id: string;
  name: I18nField;
  slug: string;
  description?: I18nField;
  is_active?: boolean;
  articles_count?: number;
}

export interface Article extends Timestamps {
  id: string;
  title: I18nField;
  slug: string;
  excerpt?: I18nField;
  content?: I18nField;
  category?: ArticleCategory;
  author?: { id: string; name: string };
  author_name?: string;
  cover_image_url?: string | null;
  featured_image_url?: string;
  tags: string[];
  views_count: number;
  comments_count: number;
  allow_comments: boolean;
  is_featured: boolean;
  status?: 'draft' | 'published' | 'archived';
  published_at?: string;
  seo_title?: I18nField;
  seo_description?: I18nField;
}

export interface Comment extends Timestamps {
  id: string;
  article_id: string;
  parent_id?: string;
  author_name: string;
  author_email: string;
  content: string;
  status: 'pending' | 'approved' | 'rejected' | 'spam';
  ip_address?: string;
  user_agent?: string;
}

export interface LiveStream {
  id: string;
  channel?: { id: string; slug: string; name: string; type?: string };
  title: string;
  description?: string | null;
  type: 'general' | 'khutba' | 'recitation' | 'lecture' | 'event' | string;
  status: 'scheduled' | 'live' | 'ended' | 'archived' | string;
  scheduled_at?: string | null;
  started_at?: string | null;
  ended_at?: string | null;
  duration_seconds?: number | null;
  current_viewers?: number;
  peak_viewers?: number;
  total_views?: number;
  is_featured?: boolean;
  thumbnail_url?: string | null;
  stream_urls?: { whep?: string; hls?: string };
  recording?: { id: string; slug: string; status: string } | null;
}

export interface VodRecording {
  id: string;
  slug: string;
  title: string;
  description?: string | null;
  channel?: { id: string; slug: string; name: string };
  hls_url?: string | null;
  mp4_url?: string | null;
  duration_seconds?: number | null;
  formatted_duration?: string;
  views_count?: number;
  thumbnail_url?: string | null;
  chapters?: Record<string, string> | null;
  published_at?: string | null;
}

export interface AudioRecitation {
  id: string;
  surah_number: number;
  surah_name: I18nField;
  reciter: string;
  audio_url: string;
  duration_seconds?: number | null;
  play_count?: number;
}

// Réponses API paginées
export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
  links: {
    first: string;
    last: string;
    prev?: string;
    next?: string;
  };
}

// Réponses API simples
export interface ApiResponse<T> {
  data: T;
}

export interface ApiListResponse<T> {
  data: T[];
}
