<?php

class LVLMigration
{
    public function __construct()
    {
        //
    }

    /**
     * @param $postType - The post type to migrate
     * @param $fields - An array of old field names and new field names
     * @return int
     */
    public function migratePostType($postType, $fields): array
    {
        $posts = get_posts([
            'post_type'      => $postType,
            'posts_per_page' => -1,
        ]);

        foreach ($posts as $post) {
            foreach ($fields as $old_Field => $new_Field) {
                $old_Object = get_field_object($old_Field, $post->ID);
                $new_Object = get_field_object($new_Field, $post->ID);

//                var_dumped($new_Object);

                if (($old_Object['value'] ?? false)) {
                    $old_type = $old_Object['type'];
                    $value = $old_Object['value'];

                    switch ($old_type) {
                        case 'link':
                            if ($new_Object['type'] === 'url') {
                                $value = $this->link_to_url($old_Object['value']);
                            } elseif ($new_Object['type'] === 'file') {
                                $url = $this->link_to_url($old_Object['value']);
                                $attachment_id = attachment_url_to_postid($url);
                                if ($attachment_id) {
                                    $value = $attachment_id;
                                }
                            }
                            break;
                    }

                    update_field($new_Field, $value, $post->ID);
                }
            }
        }

        $result = [
            'postType' => $postType,
            'fields'   => $fields,
            'count'    => count($posts),
            'IDs'      => array_map(function ($post) {
                return $post->ID;
            }, $posts),
        ];

        return $result;
    }

    public function disablePageView($postType)
    {
        $posts = get_posts([
            'post_type'      => $postType,
            'posts_per_page' => -1,
        ]);

        foreach ($posts as $post) {
            update_field('disable_page_view', true, $post->ID);
        }

        $result = [
            'postType' => $postType,
            'count'    => count($posts),
            'IDs'      => array_map(function ($post) {
                return $post->ID;
            }, $posts),
        ];

        return $result;
    }

    protected function link_to_acf_file($link)
    {
        //
    }

    protected function link_to_url($link)
    {
        if (is_string($link)) {
            $link = json_decode($link, true);
        }

        $url = $link['url'] ?? '';
//        $title = $link['title'];
//        $target = $link['target'] ? ' target="' . $link['target'] . '"' : '';
//        $rel = $link['rel'] ? ' rel="' . $link['rel'] . '"' : '';
//        $class = $link['class'] ? ' class="' . $link['class'] . '"' : '';
//        $id = $link['id'] ? ' id="' . $link['id'] . '"' : '';
//        $output = '<a href="' . $url . '"' . $target . $rel . $class . $id . '>' . $title . '</a>';
//        return $output;

        return $url;
    }
}