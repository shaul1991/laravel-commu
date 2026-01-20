/**
 * Mermaid 다이어그램 렌더링 모듈
 *
 * 아티클 내 <pre class="mermaid"> 요소를 찾아 SVG 다이어그램으로 렌더링합니다.
 */

import mermaid from 'mermaid';

// Mermaid 초기화 설정
mermaid.initialize({
    startOnLoad: false, // 수동으로 렌더링 제어
    theme: 'neutral',
    securityLevel: 'strict', // XSS 방지
    fontFamily: 'ui-sans-serif, system-ui, sans-serif',
    flowchart: {
        curve: 'basis',
        padding: 20,
    },
    sequence: {
        diagramMarginX: 50,
        diagramMarginY: 10,
        actorMargin: 50,
        width: 150,
        height: 65,
    },
});

/**
 * 페이지 내 Mermaid 다이어그램 렌더링
 *
 * @param {HTMLElement|Document} container - 검색할 컨테이너 (기본: document)
 */
export async function renderMermaidDiagrams(container = document) {
    const mermaidElements = container.querySelectorAll('pre.mermaid');

    if (mermaidElements.length === 0) {
        return;
    }

    for (const element of mermaidElements) {
        // 이미 렌더링된 경우 스킵
        if (element.getAttribute('data-mermaid-rendered') === 'true') {
            continue;
        }

        const code = element.textContent.trim();

        if (!code) {
            continue;
        }

        try {
            // 고유 ID 생성
            const id = `mermaid-${Math.random().toString(36).substr(2, 9)}`;

            // Mermaid 렌더링
            const { svg } = await mermaid.render(id, code);

            // 래퍼 div 생성
            const wrapper = document.createElement('div');
            wrapper.className = 'mermaid-wrapper my-6 flex justify-center p-4 bg-neutral-50 rounded-lg border border-neutral-200 overflow-x-auto';
            wrapper.innerHTML = svg;

            // SVG 스타일 조정
            const svgElement = wrapper.querySelector('svg');
            if (svgElement) {
                svgElement.style.maxWidth = '100%';
                svgElement.style.height = 'auto';
            }

            // 원본 요소 교체
            element.replaceWith(wrapper);
        } catch (error) {
            console.error('Mermaid rendering error:', error);

            // 에러 UI 표시
            const errorWrapper = document.createElement('div');
            errorWrapper.className = 'mermaid-error my-6 p-4 bg-red-50 border border-red-200 rounded-lg';
            errorWrapper.innerHTML = `
                <div class="flex items-center gap-2 text-red-700 font-medium mb-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>다이어그램을 렌더링할 수 없습니다</span>
                </div>
                <p class="text-sm text-red-600 mb-2">${escapeHtml(error.message || '알 수 없는 오류')}</p>
                <pre class="text-xs bg-red-100 p-2 rounded overflow-x-auto text-red-800">${escapeHtml(code)}</pre>
            `;

            element.replaceWith(errorWrapper);
        }
    }
}

/**
 * HTML 특수문자 이스케이프
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Mermaid 템플릿 반환
 */
export const mermaidTemplates = {
    flowchart: `flowchart TD
    A[시작] --> B{조건}
    B -->|Yes| C[작업 1]
    B -->|No| D[작업 2]
    C --> E[종료]
    D --> E`,

    sequence: `sequenceDiagram
    participant User
    participant Server
    User->>Server: 요청
    Server-->>User: 응답`,

    class: `classDiagram
    class Animal {
        +String name
        +makeSound()
    }
    class Dog {
        +bark()
    }
    Animal <|-- Dog`,

    state: `stateDiagram-v2
    [*] --> 대기
    대기 --> 처리중: 시작
    처리중 --> 완료: 성공
    처리중 --> 오류: 실패
    완료 --> [*]
    오류 --> 대기: 재시도`,

    er: `erDiagram
    USER ||--o{ POST : writes
    POST ||--o{ COMMENT : has`,

    gantt: `gantt
    title 프로젝트 일정
    dateFormat YYYY-MM-DD
    section 개발
    기능 A: 2024-01-01, 5d
    기능 B: 2024-01-06, 3d`,

    pie: `pie title 비율
    "A" : 40
    "B" : 30
    "C" : 30`,
};

// 전역으로 Mermaid 함수 노출 (Alpine.js에서 사용)
window.mermaid = {
    render: renderMermaidDiagrams,
    templates: mermaidTemplates,
};

// DOMContentLoaded 시 자동 렌더링
document.addEventListener('DOMContentLoaded', () => {
    renderMermaidDiagrams();
});

export default {
    render: renderMermaidDiagrams,
    templates: mermaidTemplates,
};
