<?php
/**
 * Skill Builder - generates SKILL.md and INDEX.md of the Claude skill export
 *
 * Skill layout (https://docs.claude.com/en/docs/agents-and-tools/agent-skills):
 *   {name}/SKILL.md     - frontmatter (name, description), rules, section overview
 *   {name}/INDEX.md     - every page with a short description
 *   {name}/references/  - the markdown pages of the version
 *
 * Hand-written content (rules, common mistakes) is taken from docs/{version}/.skill.md,
 * or docs/.skill.md for all versions. Its optional frontmatter "description" replaces
 * the generated skill description.
 */
class SkillBuilder {

    const REFERENCES_DIR = 'references';

    // Limits of the skill frontmatter
    const MAX_NAME_LENGTH = 64;
    const MAX_DESCRIPTION_LENGTH = 1024;

    // Page titles listed per section in SKILL.md, the rest is in INDEX.md
    const MAX_TITLES_PER_SECTION = 12;

    /** Skill name: lowercase letters, digits and hyphens, e.g. gorilla3d-v1-5 */
    public static function skillName($version) {
        $name = strtolower(SKILL_NAME . '-' . $version);
        $name = trim(preg_replace('/[^a-z0-9]+/', '-', $name), '-');
        return rtrim(substr($name, 0, self::MAX_NAME_LENGTH), '-');
    }

    /** Returns [path inside the skill folder => content] */
    public static function build($version) {
        $sections = DocIndex::collectSections(DocumentationManager::getFileTree($version) ?? [], $version);
        $custom = self::loadCustomContent($version);

        return [
            'SKILL.md' => self::buildSkill($version, $sections, $custom),
            'INDEX.md' => self::buildIndex($version, $sections),
        ];
    }

    private static function loadCustomContent($version) {
        foreach ([DOCS_PATH . '/' . $version . '/.skill.md', DOCS_PATH . '/.skill.md'] as $file) {
            if (is_file($file)) {
                $parsed = DocumentationManager::parseFrontmatterPublic(
                    str_replace(["\r\n", "\r"], "\n", file_get_contents($file)));
                return [
                    'description' => $parsed['frontmatter']['description'] ?? '',
                    'content'     => trim($parsed['content']),
                ];
            }
        }
        return ['description' => '', 'content' => ''];
    }

    private static function description($version, $custom) {
        $text = $custom['description'] !== ''
            ? $custom['description']
            : PROJECT_TITLE . ", version $version. " . PROJECT_SUMMARY . ' ' . SKILL_TRIGGER;

        // Single line, no XML tags, within the length limit
        $text = trim(preg_replace('/\s+/', ' ', str_replace(['<', '>'], '', $text)));
        if (mb_strlen($text) > self::MAX_DESCRIPTION_LENGTH) {
            $text = mb_substr($text, 0, self::MAX_DESCRIPTION_LENGTH - 4);
            $text = preg_replace('/\s+\S*$/u', '', $text) . ' ...';
        }
        return $text;
    }

    private static function referencePath($path) {
        return self::REFERENCES_DIR . ($path !== '' ? '/' . $path : '');
    }

    /** Markdown link target, spaces and special characters encoded */
    private static function linkTarget($path) {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    private static function buildSkill($version, $sections, $custom) {
        $refs = self::REFERENCES_DIR;
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        // JSON strings are valid YAML double-quoted scalars
        $out  = "---\n";
        $out .= "name: " . self::skillName($version) . "\n";
        $out .= "description: " . json_encode(self::description($version, $custom), $flags) . "\n";
        $out .= "---\n\n";

        $out .= "# " . PROJECT_TITLE . " ($version)\n\n";
        $out .= PROJECT_SUMMARY . "\n\n";
        foreach (PROJECT_NOTES as $note) {
            $out .= "- $note\n";
        }
        $out .= "\n";

        if ($custom['content'] !== '') {
            $out .= $custom['content'] . "\n\n";
        }

        $out .= "## How to use this documentation\n\n";
        $out .= "The complete documentation of version $version is bundled with this skill as Markdown files in `$refs/`.\n\n";
        $out .= "1. Find the relevant pages in the section overview below, in [INDEX.md](INDEX.md) " .
                "(every page with a short description), or by searching `$refs/` for a class, unit or property name.\n";
        $out .= "2. Read the relevant pages before writing or changing code. Only use classes, properties, methods " .
                "and events that are documented there; do not guess API members.\n";
        $out .= "3. Load only the pages the task needs, not whole folders.\n";
        $out .= "4. Links between pages are relative and can be followed directly.\n\n";

        $out .= "## Sections\n\n";
        foreach ($sections as $section) {
            $titles = array_column($section['pages'], 'title');
            $more = count($titles) - self::MAX_TITLES_PER_SECTION;
            if ($more > 0) {
                $titles = array_slice($titles, 0, self::MAX_TITLES_PER_SECTION);
                $titles[] = "... ($more more in INDEX.md)";
            }
            $out .= "- **{$section['title']}** (`" . self::referencePath($section['path']) . "/`): " .
                    implode(', ', $titles) . "\n";
        }

        return $out;
    }

    private static function buildIndex($version, $sections) {
        $out  = "# " . PROJECT_TITLE . " ($version) - page index\n\n";
        $out .= "All pages in `" . self::REFERENCES_DIR . "/`, grouped by section, in reading order.\n";

        foreach ($sections as $section) {
            $out .= "\n## {$section['title']}\n\n";
            foreach ($section['pages'] as $page) {
                $out .= "- [{$page['title']}](" . self::linkTarget(self::referencePath($page['path'])) . ")";
                $out .= $page['description'] !== '' ? ": {$page['description']}\n" : "\n";
            }
        }
        return $out;
    }
}
