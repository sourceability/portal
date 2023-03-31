<?php

namespace Sourceability\Portal\Tests\Bundle\SampleApp\Spells;

use Sourceability\Portal\Spell\Spell;
use Sourceability\Portal\Tests\Bundle\SampleApp\Spells\DTO\ReviewComment;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @implements Spell<string, array<ReviewComment>>
 */
class CodeReviewSpell implements Spell
{
    public function __construct(private readonly DenormalizerInterface $denormalizer)
    {
    }

    public function getExamples(): array
    {
        return [
// https://github.com/firefly-iii/firefly-iii/commit/8e56fa4ef8d570c3f2fb74da61fd34e3c78f2467.patch
<<<'PATCH'
From 8e56fa4ef8d570c3f2fb74da61fd34e3c78f2467 Mon Sep 17 00:00:00 2001
From: James Cole <james@firefly-iii.org>
Date: Tue, 14 Mar 2023 18:09:44 +0100
Subject: [PATCH] Fix https://github.com/firefly-iii/firefly-iii/issues/7221

---
 .../Controllers/Rule/SelectController.php     |  1 +
 .../Engine/RuleEngineInterface.php            |  6 ++++
 .../Engine/SearchRuleEngine.php               | 35 +++++++++++++++----
 3 files changed, 35 insertions(+), 7 deletions(-)

diff --git a/app/Http/Controllers/Rule/SelectController.php b/app/Http/Controllers/Rule/SelectController.php
index bb4f130584..fbaebc00cc 100644
--- a/app/Http/Controllers/Rule/SelectController.php
+++ b/app/Http/Controllers/Rule/SelectController.php
@@ -164,6 +164,7 @@ public function testTriggers(TestRuleFormRequest $request): JsonResponse

         // set rules:
         $newRuleEngine->setRules(new Collection([$rule]));
+        $newRuleEngine->setRefreshTriggers(false);
         $collection = $newRuleEngine->find();
         $collection = $collection->slice(0, 20);

diff --git a/app/TransactionRules/Engine/RuleEngineInterface.php b/app/TransactionRules/Engine/RuleEngineInterface.php
index 111fdb6231..5d58065fa1 100644
--- a/app/TransactionRules/Engine/RuleEngineInterface.php
+++ b/app/TransactionRules/Engine/RuleEngineInterface.php
@@ -73,4 +73,10 @@ public function setRules(Collection $rules): void;
      * @param  User  $user
      */
     public function setUser(User $user): void;
+
+    /**
+     * @param  bool  $refreshTriggers
+     * @return void
+     */
+    public function setRefreshTriggers(bool $refreshTriggers): void;
 }
diff --git a/app/TransactionRules/Engine/SearchRuleEngine.php b/app/TransactionRules/Engine/SearchRuleEngine.php
index f12e775102..bf22a98065 100644
--- a/app/TransactionRules/Engine/SearchRuleEngine.php
+++ b/app/TransactionRules/Engine/SearchRuleEngine.php
@@ -47,6 +47,7 @@ class SearchRuleEngine implements RuleEngineInterface
     private array      $resultCount;
     private Collection $rules;
     private User       $user;
+    private bool       $refreshTriggers;

     public function __construct()
     {
@@ -54,6 +55,9 @@ public function __construct()
         $this->groups      = new Collection();
         $this->operators   = [];
         $this->resultCount = [];
+
+        // always collect the triggers from the database, unless indicated otherwise.
+        $this->refreshTriggers = true;
     }

     /**
@@ -97,8 +101,13 @@ private function findStrictRule(Rule $rule): Collection
     {
         Log::debug(sprintf('Now in findStrictRule(#%d)', $rule->id ?? 0));
         $searchArray = [];
-
-        $triggers = $rule->ruleTriggers()->orderBy('order', 'ASC')->get();
+        $triggers    = [];
+        if ($this->refreshTriggers) {
+            $triggers = $rule->ruleTriggers()->orderBy('order', 'ASC')->get();
+        }
+        if (!$this->refreshTriggers) {
+            $triggers = $rule->ruleTriggers;
+        }

         /** @var RuleTrigger $ruleTrigger */
         foreach ($triggers as $ruleTrigger) {
@@ -224,11 +233,15 @@ public function setUser(User $user): void
     private function findNonStrictRule(Rule $rule): Collection
     {
         // start a search query for individual each trigger:
-        $total = new Collection();
-        $count = 0;
-
-        /** @var Collection $triggers */
-        $triggers = $rule->ruleTriggers;
+        $total    = new Collection();
+        $count    = 0;
+        $triggers = [];
+        if ($this->refreshTriggers) {
+            $triggers = $rule->ruleTriggers()->orderBy('order', 'ASC')->get();
+        }
+        if (!$this->refreshTriggers) {
+            $triggers = $rule->ruleTriggers;
+        }

         /** @var RuleTrigger $ruleTrigger */
         foreach ($triggers as $ruleTrigger) {
@@ -549,4 +562,12 @@ public function setRules(Collection $rules): void
             }
         }
     }
+
+    /**
+     * @param  bool  $refreshTriggers
+     */
+    public function setRefreshTriggers(bool $refreshTriggers): void
+    {
+        $this->refreshTriggers = $refreshTriggers;
+    }
 }
PATCH
            ,

        ];
    }

    public function getSchema(): array
    {
        return [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'context' => [
                        'oneOf' => [
                            [
                                'type' => 'object',
                                'description' => 'When the comment applies to a line of code.',
                                'properties' => [
                                    'path' => [
                                        'type' => 'string',
                                    ],
                                    'lineNumber' => [
                                        'type' => 'int',
                                    ],
                                ]
                            ],
                            [
                                'type' => 'string',
                                'enum' => ['overall_approach', 'commit_message'],
                            ],
                        ],
                    ],
                    'comment' => [
                        'type' => 'string',
                        'description' => 'The code review comment',
                    ],
                    'emoji' => [
                        'type' => 'string',
                        'description' => 'An emoji to summarize the comment sentiment',
                        'examples' => ['👍', '🤔', '🤯', '🔥', '🎉', '💯'],
                        'minLength' => 1,
                        'maxLength' => 1,
                    ],
                ],
                'required' => ['context', 'comment', 'emoji'],
            ],
        ];
    }

    public function getPrompt($input): string
    {
        return <<<PROMPT
You are a snarky code reviewer that only makes joke comments, like puns.
Review the following GIT patch.

```patch
{$input}
```
PROMPT;
    }

    public function transcribe(mixed $completionValue): array
    {
        return $this->denormalizer->denormalize(
            $completionValue,
            ReviewComment::class . '[]',
        );
    }
}
