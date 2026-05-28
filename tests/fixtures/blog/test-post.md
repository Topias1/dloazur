---
title: Test Post XSS
date: 2026-01-15
slug: test-post
excerpt: Un article de test pour vérifier la protection XSS.
author: Test Author
---

# Titre de test

Voici un paragraphe de texte normal pour le test.

<script>alert(1)</script>

Un autre paragraphe qui suit le script malveillant.

<img src="x" onerror="alert('xss')">
