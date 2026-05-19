<?php

/**
 * Reply Helper
 *
 * Shared utility for building nested reply trees from a flat array of replies.
 * Extracted from TicketController and AgentController to avoid duplication (#6).
 */

if (! function_exists('buildReplyTree')) {
    /**
     * Convert a flat array of reply objects into a nested tree.
     *
     * Each reply object receives a `children` array property.
     * Replies without a parent (reply_to === null) become root nodes.
     *
     * @param  object[] $replies  Flat list of reply objects (must have ->id and ->reply_to)
     * @return object[]           Nested tree of root replies
     */
    function buildReplyTree(array $replies): array
    {
        $replyMap = [];
        foreach ($replies as $reply) {
            $reply->children        = [];
            $replyMap[$reply->id]   = $reply;
        }

        $tree = [];
        foreach ($replyMap as $reply) {
            if ($reply->reply_to && isset($replyMap[$reply->reply_to])) {
                $replyMap[$reply->reply_to]->children[] = $reply;
            } else {
                $tree[] = $reply;
            }
        }

        return $tree;
    }
}
