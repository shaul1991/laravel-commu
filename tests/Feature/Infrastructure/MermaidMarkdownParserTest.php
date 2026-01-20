<?php

declare(strict_types=1);

use App\Infrastructure\Services\MermaidMarkdownParser;

describe('MermaidMarkdownParser', function () {
    it('mermaid 코드 블록을 pre.mermaid 태그로 변환한다', function () {
        $parser = new MermaidMarkdownParser;
        $markdown = <<<'MD'
# 테스트

```mermaid
flowchart TD
    A[시작] --> B[종료]
```
MD;

        $html = $parser->parse($markdown);

        expect($html)->toContain('<pre class="mermaid">');
        expect($html)->toContain('flowchart TD');
        expect($html)->toContain('A[시작] --> B[종료]');
        expect($html)->toContain('</pre>');
    });

    it('일반 코드 블록은 기존대로 code 태그로 변환한다', function () {
        $parser = new MermaidMarkdownParser;
        $markdown = <<<'MD'
```php
echo "Hello World";
```
MD;

        $html = $parser->parse($markdown);

        expect($html)->toContain('<code');
        // 마크다운 파서가 따옴표를 HTML 엔티티로 변환함
        expect($html)->toContain('echo');
        expect($html)->toContain('Hello World');
        expect($html)->not->toContain('<pre class="mermaid">');
    });

    it('여러 mermaid 코드 블록을 모두 변환한다', function () {
        $parser = new MermaidMarkdownParser;
        $markdown = <<<'MD'
```mermaid
flowchart TD
    A --> B
```

텍스트

```mermaid
sequenceDiagram
    User->>Server: Request
```
MD;

        $html = $parser->parse($markdown);

        // 두 개의 mermaid 블록이 변환되어야 함
        expect(substr_count($html, '<pre class="mermaid">'))->toBe(2);
        expect($html)->toContain('flowchart TD');
        expect($html)->toContain('sequenceDiagram');
    });

    it('mermaid 코드 블록과 일반 코드 블록이 혼합된 경우 올바르게 처리한다', function () {
        $parser = new MermaidMarkdownParser;
        $markdown = <<<'MD'
```mermaid
flowchart TD
    A --> B
```

```javascript
console.log('test');
```
MD;

        $html = $parser->parse($markdown);

        expect($html)->toContain('<pre class="mermaid">');
        expect($html)->toContain('flowchart TD');
        expect($html)->toContain('<code');
        expect($html)->toContain('console.log');
    });

    it('mermaid 코드 내용이 HTML 이스케이프되지 않는다', function () {
        $parser = new MermaidMarkdownParser;
        $markdown = <<<'MD'
```mermaid
flowchart TD
    A[시작] --> B{조건}
    B -->|Yes| C[완료]
```
MD;

        $html = $parser->parse($markdown);

        // HTML 엔티티로 변환되면 안됨 (Mermaid.js가 파싱해야 함)
        expect($html)->toContain('A[시작] --> B{조건}');
        expect($html)->toContain('-->|Yes|');
    });

    it('빈 mermaid 코드 블록을 처리한다', function () {
        $parser = new MermaidMarkdownParser;
        $markdown = <<<'MD'
```mermaid
```
MD;

        $html = $parser->parse($markdown);

        expect($html)->toContain('<pre class="mermaid">');
        expect($html)->toContain('</pre>');
    });

    it('XSS 공격을 방지한다', function () {
        $parser = new MermaidMarkdownParser;
        $markdown = <<<'MD'
```mermaid
flowchart TD
    A[<script>alert('xss')</script>] --> B
```
MD;

        $html = $parser->parse($markdown);

        // 스크립트 태그가 이스케이프되어야 함
        expect($html)->not->toContain('<script>');
        // &lt;script 형식으로 이스케이프됨
        expect($html)->toContain('&lt;script');
    });
});
