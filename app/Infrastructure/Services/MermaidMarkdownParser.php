<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use Illuminate\Support\Str;

/**
 * Mermaid 다이어그램을 지원하는 마크다운 파서
 *
 * Mermaid 코드 블록(```mermaid)을 인식하여
 * 프론트엔드에서 렌더링 가능한 <pre class="mermaid">로 변환합니다.
 */
final class MermaidMarkdownParser implements MarkdownParserInterface
{
    /**
     * 마크다운을 HTML로 변환
     *
     * 1. Mermaid 코드 블록을 임시 플레이스홀더로 대체
     * 2. 일반 마크다운 파싱
     * 3. 플레이스홀더를 <pre class="mermaid">로 복원
     */
    public function parse(string $markdown): string
    {
        // Mermaid 코드 블록 추출 및 플레이스홀더로 대체
        $mermaidBlocks = [];
        $counter = 0;

        $processedMarkdown = preg_replace_callback(
            '/```mermaid\s*([\s\S]*?)```/m',
            function ($matches) use (&$mermaidBlocks, &$counter) {
                // 마크다운에서 특수 문자로 해석되지 않도록 UUID 형식 사용
                $placeholder = sprintf('MERMAIDBLOCK%08d', $counter);
                $mermaidBlocks[$placeholder] = trim($matches[1]);
                $counter++;

                return $placeholder;
            },
            $markdown
        );

        // 일반 마크다운 파싱
        $html = Str::markdown($processedMarkdown ?? $markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        // Mermaid 플레이스홀더를 <pre class="mermaid">로 복원
        foreach ($mermaidBlocks as $placeholder => $code) {
            // XSS 방지를 위해 HTML 엔티티 이스케이프
            // 단, Mermaid.js가 파싱할 수 있도록 기본 문법은 유지
            $escapedCode = $this->escapeMermaidCode($code);

            $mermaidHtml = sprintf(
                '<pre class="mermaid">%s</pre>',
                $escapedCode
            );

            // 플레이스홀더가 <p> 태그로 감싸져 있을 수 있으므로 처리
            $html = str_replace(
                ["<p>{$placeholder}</p>", $placeholder],
                $mermaidHtml,
                $html
            );
        }

        return $html;
    }

    /**
     * Mermaid 코드에서 위험한 HTML 태그만 이스케이프
     *
     * Mermaid 문법(괄호, 화살표 등)은 유지하면서
     * XSS 공격에 사용될 수 있는 태그만 이스케이프합니다.
     */
    private function escapeMermaidCode(string $code): string
    {
        // 스크립트 태그 및 위험한 HTML 태그 이스케이프
        $dangerous = ['<script', '</script', '<iframe', '</iframe', '<object', '</object', '<embed', '</embed', 'javascript:', 'onerror=', 'onclick=', 'onload='];

        foreach ($dangerous as $pattern) {
            $code = str_ireplace($pattern, htmlspecialchars($pattern, ENT_QUOTES, 'UTF-8'), $code);
        }

        return $code;
    }
}
