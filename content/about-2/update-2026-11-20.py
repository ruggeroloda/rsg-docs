#!/usr/bin/env python3
"""About page (page 116) update, 20 Nov 2026 (theme/BRIEF-TRUST-DETAILS-2026-11-20.md; Ruggero's choices the same day):
visits number out (both places), mailing list stays, review count from the site ([rsg_review_count], theme
includes/rsg-home.php), "found" typo, heading "Blog ?". The Italian link stays (Ruggero undecided).
usage: update-2026-11-20.py IN OUT  (live or staging content)."""
import sys
s = open(sys.argv[1], encoding="utf-8").read()

def rep(old, new):
    global s
    assert s.count(old) == 1, ("not found once", old[:60])
    s = s.replace(old, new)

rep('''<li>200,000 -> the number of visitors to our website every month</li>
<li>35,000 -> the number of runners on our mailing list</li>''',
    '''<li>[rsg_review_count] -> the number of running shoe reviews we have published since 2009</li>
<li>35,000 -> the number of runners on our mailing list</li>''')
rep('(fun fact: you can now found <a', '(fun fact: you can also find <a')
rep('<h2>Why a Running Shoes Guru Blog ?</h2>', '<h2>Why a Running Shoes Guru Blog?</h2>')
rep('<h2>Traffic and Advertising Info</h2>', '<h2>Advertising Info</h2>')
rep('Running Shoes Guru receives around 200,000 visits every month from runners or runners-to-be all around the world. We do not accept direct sponsorship',
    'We do not accept direct sponsorship')
open(sys.argv[2], "w", encoding="utf-8").write(s)
