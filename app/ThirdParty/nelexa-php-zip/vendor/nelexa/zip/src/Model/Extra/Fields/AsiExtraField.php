<?php

declare(strict_types=1);

/*
 * This file is part of the nelexa/zip package.
 * (c) Ne-Lexa <http://github.com/Ne-Lexa/php-zip>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpZip\Model\Extra\Fields;

use PhpZip\Constants\UnixStat;
use PhpZip\Exception\Crc32Exception;
use PhpZip\Model\Extra\ZipExtraField;
use PhpZip\Model\ZipEntry;

/**
 * ASi Unix Extra Field:
 * ====================.
 *
 * The following is the layout of the ASi extra block for Unix.  The
 * local-header and central-header versions are identical.
 * (Last Revision 19960916)
 *
 * Value         Size        Description
 * -----         ----        -----------
 * (Unix3) 0x756e        Short       tag for this extra block type ("nu")
 * TSize         Short       total data size for this block
 * CRC           Long        CRC-32 of the remaining data
 * Mode          Short       file permissions
 * SizDev        Long        symlink'd size OR major/minor dev num
 * UID           Short       user ID
 * GID           Short       group ID
 * (var.)        variable    symbolic link filename
 *
 * Mode is the standard Unix st_mode field from struct stat, containing
 * user/group/other permissions, setuid/setgid and symlink info, etc.
 *
 * If Mode indicates that this file is a symbolic link, SizDev is the
 * size of the file to which the link points.  Otherwise, if the file
 * is a device, SizDev contains the standard Unix st_rdev field from
 * struct stat (includes the major and minor numbers of the device).
 * SizDev is undefined in other cases.
 *
 * If Mode indicates that the file is a symbolic link, the final field
 * will be the name of the file to which the link points.  The file-
 * name length can be inferred from TSize.
 *
 * [Note that TSize may incorrectly refer to the data size not counting
 * the CRC; i.e., it may be four bytes too small.]
 *
 * @see ftp://ftp.info-zip.org/pub/infozip/doc/appnote-iz-latest.zip Info-ZIP version Specification
 */
final class AsiExtraField implements ZipExtraField
{
    /** @var int Header id */
    public const HEADER_ID = 0x756E;

    public const USER_GID_PID = 1000;

    /** Bits used for permissions (and sticky bit). */
    public const PERM_MASK = 07777;

    /** @var int Standard Unix stat(2) file mode. */
    private int $mode;

    /** @var int User ID. */
    private int $uid;

    /** @var int Group ID. */
    private int $gid;

    /**
     * @var string File this entry points to, if it is a symbolic link.
     *             Empty string - if entry is not a symbolic link.
     */
    private string $link;

    public function __construct(int $mode, int $uid = self::USER_GID_PID, int $gid = self::USER_GID_PID, string $link = '')
    {
        $this->mode = $mode;
        $this->uid = $uid;
        $this->gid = $gid;
        $this->link = $link;
    }

    /**
     * Returns the Header ID (type) of this Extra Field.
     * The Header ID is an unsigned short integer (two bytes)
     * which must be constant during the life cycle of this object.
     */
    public function getHeaderId(): int
    {
        return self::HEADER_ID;
    }

    /**
     * Populate data from this array as if it was in local file data.
     *
     * @param string        $buffer the buffer to read data from
     * @param ZipEntry|null $entry  optional zip entry
     *
     * @throws Crc32Exception
     *
     * @return AsiExtraField
     */
    public static function unpackLocalFileData(string $buffer, ?ZipEntry $entry = null): self
    {
        $givenChecksum = unpack('V', $buffer)[1];
        $buffer = substr($buffer, 4);
        $realChecksum = crc32($buffer);

        if ($givenChecksum !== $realChecksum) {
            throw new Crc32Exception('Asi Unix Extra Filed Data', $givenChecksum, $realChecksum);
        }

        [
            'mode' => $mode,
            'linkSize' => $linkSize,
            'uid' => $uid,
            'gid' => $gid,
        ] = unpack('vmode/VlinkSize/vuid/vgid', $buffer);
        $link = '';

        if ($linkSize > 0) {
            $link = substr($buffer, 10);
        }

        return new self($mode, $uid, $gid, $link);
    }

    /**
     * Populate data from this array as if it was in central directory data.
     *
     * @param string        $buffer the buffer to read data from
     * @param ZipEntry|null $entry  optional zip entry
     *
     * @throws Crc32Exception
     *
     * @return AsiExtraField
     */
    public static function unpackCentralDirData(string $buffer, ?ZipEntry $entry = null): self
    {
        return self::unpackLocalFileData($buffer, $entry);
    }

    /**
     * The actual data to put into local file data - without Header-ID
     * or length specifier.
     *
     * @return string the data
     */
    public function packLocalFileData(): string
    {
        $data = pack(
            'vVvv',
            $this->mode,
            \strlen($this->link),
            $this->uid,
            $this->gid
        ) . $this->link;

        return pack('V', crc32($data)) . $data;
    }

    /**
     * The actual data to put into central directory - without Header-ID or
     * length specifier.
     *
     * @return string the data
     */
    public function packCentralDirData(): string
    {
        return $this->packLocalFileData();
    }

    /**
     * Name of linked file.
     *
     * @return string name of the file this entry links to if it is a
     *                symbolic link, the empty string otherwise
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * Indicate that this entry is a symbolic link to the given filename.
     *
     * @param string $link name of the file this entry links to, empty
     *                     string if it is not a symbolic link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
        $this->mode = $this->getPermissionsMode($this->mode);
    }

    /**
     * Is this entry a symbolic link?
     *
     * @return bool true if this is a symbolic link
     */
    public function isLink(): bool
    {
        return !empty($this->link);
    }

    /**
     * Get the file mode for given permissions with the correct file type.
     *
     * @param int $mode the mode
     *
     * @return int the type with the mode
     */
    private function getPermissionsMode(int $mode): int
    {
        $type = 0;

        if ($this->isLink()) {
            $type = UnixStat::UNX_IFLNK;
        } elseif (($mode & UnixStat::UNX_IFREG) !== 0) {
            $type = UnixStat::UNX_IFREG;
        } elseif (($mode & UnixStat::UNX_IFDIR) !== 0) {
            $type = UnixStat::UNX_IFDIR;
        }

        return $type | ($mode & self::PERM_MASK);
    }

    /**
     * Is this entry a directory?
     *
     * @return bool true if this entry is a directory
     */
    public function isDirectory(): bool
    {
        return ($this->mode & UnixStat::UNX_IFDIR) !== 0 && !$this->isLink();
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function setMode(int $mode): void
    {
        $this->mode = $this->getPermissionsMode($mode);
    }

    public function getUserId(): int
    {
        return $this->uid;
    }

    public function setUserId(int $uid): void
    {
        $this->uid = $uid;
    }

    public function getGroupId(): int
    {
        return $this->gid;
    }

    public function setGroupId(int $gid): void
    {
        $this->gid = $gid;
    }

    public function __toString(): string
    {
        return sprintf(
            '0x%04x ASI: Mode=%o UID=%d GID=%d Link="%s',
            self::HEADER_ID,
            $this->mode,
            $this->uid,
            $this->gid,
            $this->link
        );
    }
}
