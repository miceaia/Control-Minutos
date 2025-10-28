<?php
/**
 * Helper utilities to communicate with external plugins and services.
 *
 * @package Control_Minutos
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Consolidates integration logic with LearnDash and Advanced Video Player Pro.
 */
class Control_Minutos_Integrations {

    /**
     * Return a keyed list of LearnDash courses when available.
     *
     * @return array<int,string>
     */
    public function get_learndash_courses() {
        $courses = array();

        if ( post_type_exists( 'sfwd-courses' ) ) {
            $posts = get_posts(
                array(
                    'post_type'      => 'sfwd-courses',
                    'posts_per_page' => -1,
                    'post_status'    => array( 'publish', 'private' ),
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                    'fields'         => 'ids',
                )
            );

            foreach ( $posts as $course_id ) {
                $courses[ $course_id ] = get_the_title( $course_id );
            }
        }

        /**
         * Filter the list of resolved LearnDash courses.
         *
         * @param array<int,string> $courses Courses resolved from LearnDash.
         */
        return apply_filters( 'control_minutos_learndash_courses', $courses );
    }

    /**
     * Return a map of LearnDash lessons grouped by course when available.
     *
     * @return array<int,array{title:string,course_id:int}>
     */
    public function get_learndash_lessons() {
        $lessons = array();

        if ( post_type_exists( 'sfwd-lessons' ) ) {
            $posts = get_posts(
                array(
                    'post_type'      => 'sfwd-lessons',
                    'posts_per_page' => -1,
                    'post_status'    => array( 'publish', 'private' ),
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                )
            );

            foreach ( $posts as $lesson ) {
                $course_id = 0;

                if ( function_exists( 'learndash_get_course_id' ) ) {
                    $course_id = (int) learndash_get_course_id( $lesson );
                }

                if ( ! $course_id ) {
                    $course_id = (int) get_post_meta( $lesson->ID, 'course_id', true );
                }

                $lessons[ $lesson->ID ] = array(
                    'title'     => get_the_title( $lesson->ID ),
                    'course_id' => $course_id,
                );
            }
        }

        /**
         * Filter the list of resolved LearnDash lessons.
         *
         * @param array<int,array{title:string,course_id:int}> $lessons Lessons resolved from LearnDash.
         */
        return apply_filters( 'control_minutos_learndash_lessons', $lessons );
    }

    /**
     * Determine whether LearnDash appears to be active.
     *
     * @return bool
     */
    public function is_learndash_active() {
        return post_type_exists( 'sfwd-courses' ) || class_exists( 'SFWD_LMS' );
    }

    /**
     * Return the current course/lesson context when viewing LearnDash content.
     *
     * @return array{course_id:int,lesson_id:int}
     */
    public function get_current_learndash_context() {
        $context = array(
            'course_id' => 0,
            'lesson_id' => 0,
        );

        global $post;

        if ( ! $post instanceof WP_Post ) {
            return $context;
        }

        $course_id = 0;

        if ( function_exists( 'learndash_get_course_id' ) ) {
            $course_id = (int) learndash_get_course_id( $post );
        }

        if ( ! $course_id && post_type_exists( 'sfwd-courses' ) && 'sfwd-courses' === $post->post_type ) {
            $course_id = (int) $post->ID;
        }

        $lesson_id = 0;

        if ( 'sfwd-lessons' === $post->post_type ) {
            $lesson_id = (int) $post->ID;
        } elseif ( function_exists( 'learndash_get_lesson_id' ) && $course_id ) {
            $lesson_id = (int) learndash_get_lesson_id( $post->ID, $course_id );
        }

        if ( $course_id ) {
            $context['course_id'] = $course_id;
        }

        if ( $lesson_id ) {
            $context['lesson_id'] = $lesson_id;
        }

        return $context;
    }

    /**
     * Attempt to resolve course/lesson IDs using a video identifier.
     *
     * @param string $video_id Video identifier coming from the player.
     *
     * @return array{course_id:int,lesson_id:int}
     */
    public function resolve_context_from_video( $video_id ) {
        $context = array(
            'course_id' => 0,
            'lesson_id' => 0,
        );

        if ( empty( $video_id ) ) {
            return $context;
        }

        $video_post = $this->maybe_get_avppro_video_post( $video_id );

        if ( ! $video_post ) {
            return $context;
        }

        $lesson_id = (int) get_post_meta( $video_post->ID, '_control_minutos_lesson_id', true );
        $course_id = (int) get_post_meta( $video_post->ID, '_control_minutos_course_id', true );

        if ( ! $lesson_id && function_exists( 'learndash_get_lesson_id' ) ) {
            $lesson_id = (int) learndash_get_lesson_id( $video_post->ID );
        }

        if ( ! $course_id && function_exists( 'learndash_get_course_id' ) ) {
            $course_id = (int) learndash_get_course_id( $video_post->ID );
        }

        if ( $course_id ) {
            $context['course_id'] = $course_id;
        }

        if ( $lesson_id ) {
            $context['lesson_id'] = $lesson_id;
        }

        return $context;
    }

    /**
     * Try to retrieve the WP_Post representing a synced AVP video.
     *
     * @param string $video_id Bunny/AVP identifier.
     *
     * @return WP_Post|null
     */
    protected function maybe_get_avppro_video_post( $video_id ) {
        $post_types = array(
            'miceaia-video',
            'miceaia_avpro_video',
            'avppro-video',
            'advanced-video',
            'advanced_video',
        );

        foreach ( $post_types as $post_type ) {
            if ( ! post_type_exists( $post_type ) ) {
                continue;
            }

            $query = get_posts(
                array(
                    'post_type'      => $post_type,
                    'posts_per_page' => 1,
                    'post_status'    => 'any',
                    'meta_query'     => array(
                        'relation' => 'OR',
                        array(
                            'key'   => '_video_id',
                            'value' => $video_id,
                        ),
                        array(
                            'key'   => 'video_id',
                            'value' => $video_id,
                        ),
                        array(
                            'key'   => 'bunny_video_id',
                            'value' => $video_id,
                        ),
                    ),
                )
            );

            if ( ! empty( $query ) ) {
                return $query[0];
            }
        }

        return null;
    }

    /**
     * Locate a duration (in seconds) for a given video when synced by AVP.
     *
     * @param string $video_id Video identifier coming from the player.
     *
     * @return int Duration in seconds, 0 when unknown.
     */
    public function get_avppro_duration( $video_id ) {
        $post = $this->maybe_get_avppro_video_post( $video_id );

        if ( ! $post ) {
            return 0;
        }

        $meta_keys = array(
            '_duration_seconds',
            'duration_seconds',
            '_video_duration',
            'video_duration',
            '_bunny_duration',
            'bunny_duration',
        );

        foreach ( $meta_keys as $meta_key ) {
            $value = get_post_meta( $post->ID, $meta_key, true );

            if ( '' === $value ) {
                continue;
            }

            $seconds = $this->normalize_duration_to_seconds( $value );

            if ( $seconds > 0 ) {
                return $seconds;
            }
        }

        return 0;
    }

    /**
     * Normalize a duration (seconds or HH:MM:SS) to seconds.
     *
     * @param mixed $value Raw meta value.
     *
     * @return int
     */
    protected function normalize_duration_to_seconds( $value ) {
        if ( is_numeric( $value ) ) {
            $seconds = (int) $value;

            return max( 0, $seconds );
        }

        if ( is_string( $value ) && preg_match( '/^(\d+):(\d{2})(?::(\d{2}))?$/', trim( $value ), $matches ) ) {
            $hours   = isset( $matches[3] ) ? (int) $matches[1] : 0;
            $minutes = isset( $matches[3] ) ? (int) $matches[2] : (int) $matches[1];
            $seconds = isset( $matches[3] ) ? (int) $matches[3] : (int) $matches[2];

            if ( $hours ) {
                return ( $hours * 3600 ) + ( $minutes * 60 ) + $seconds;
            }

            return ( $minutes * 60 ) + $seconds;
        }

        return 0;
    }
}

